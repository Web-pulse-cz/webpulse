<?php

namespace App\Http\Controllers\Admin\PriceOffer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Invoice\InvoiceResource;
use App\Http\Resources\Admin\PriceOffer\PriceOfferResource;
use App\Http\Resources\Admin\PriceOffer\PriceOfferSimpleResource;
use App\Jobs\Fakturoid\SyncInvoiceToFakturoidJob;
use App\Models\Invoice\Invoice;
use App\Models\PriceOffer\PriceOffer;
use App\Models\Site\Site;
use App\Traits\HasFiles;
use App\Traits\Siteable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PriceOfferController extends Controller
{
    use HasFiles, Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = PriceOffer::with('client')
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->get('client_id'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->get('project_id'));
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->has('paginate')) {
            $offers = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => PriceOfferSimpleResource::collection($offers->items()),
                'total' => $offers->total(),
                'perPage' => $offers->perPage(),
                'currentPage' => $offers->currentPage(),
                'lastPage' => $offers->lastPage(),
            ]);
        }

        return Response::json(PriceOfferSimpleResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $offer = PriceOffer::find($id);
            if (! $offer) {
                App::abort(404);
            }
        } else {
            $offer = new PriceOffer;
            $offer->user_id = Auth::id();
            $offer->code = $offer->generateCode();
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable|integer|exists:clients,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'tax_rate_id' => 'nullable|integer|exists:tax_rates,id',
            'valid_to' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $offer->fill($request->except(['items', 'sites', 'code']));
            $offer->save();

            // Sync items
            $items = $request->get('items', []);
            $offer->items()->delete();
            $position = 0;
            $totalWithoutVat = 0;
            $totalVat = 0;
            $totalWithVat = 0;

            foreach ($items as $item) {
                $qty = $item['quantity'] ?? 1;
                $unitPrice = $item['unit_price_without_vat'] ?? 0;
                $vatRate = $item['vat_rate'] ?? 0;
                $itemWithoutVat = $qty * $unitPrice;
                $itemVat = $itemWithoutVat * ($vatRate / 100);
                $itemWithVat = $itemWithoutVat + $itemVat;

                $offer->items()->create([
                    'name' => $item['name'] ?? '',
                    'description' => $item['description'] ?? null,
                    'quantity' => $qty,
                    'unit_name' => $item['unit_name'] ?? null,
                    'unit_price_without_vat' => $unitPrice,
                    'vat_rate' => $vatRate,
                    'total_without_vat' => $itemWithoutVat,
                    'total_vat' => $itemVat,
                    'total_with_vat' => $itemWithVat,
                    'position' => $position++,
                ]);

                $totalWithoutVat += $itemWithoutVat;
                $totalVat += $itemVat;
                $totalWithVat += $itemWithVat;
            }

            $offer->total_without_vat = $totalWithoutVat;
            $offer->total_vat = $totalVat;
            $offer->total_with_vat = $totalWithVat;
            $offer->save();

            // Auto-assign site from header
            $siteId = $this->handleSite($request->header('X-Site-Hash'));
            $sites = $request->get('sites', [$siteId]);
            $this->saveSites($offer, $sites);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání cenové nabídky: '.$e->getMessage()], 500);
        }

        // Generate PDF
        $this->generatePdf($offer->fresh(['items', 'client', 'sites']));

        return Response::json(PriceOfferResource::make($offer->fresh(['items', 'client', 'sites'])));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $offer = PriceOffer::with(['items', 'client', 'sites'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $offer) {
            App::abort(404);
        }

        return Response::json(PriceOfferResource::make($offer));
    }

    public function destroy(int $id): JsonResponse
    {
        $offer = PriceOffer::find($id);
        if (! $offer) {
            App::abort(404);
        }

        $offer->removeAllFiles();
        $offer->items()->delete();
        $offer->delete();

        return Response::json();
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        $offer = PriceOffer::with('items')->find($id);
        if (! $offer) {
            App::abort(404);
        }

        if ($offer->status !== 'draft' && $offer->status !== 'sent') {
            return Response::json(['message' => 'Cenová nabídka nemůže být přijata v tomto stavu.'], 400);
        }

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:proforma,invoice',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $offer->status = 'accepted';
            $offer->accepted_at = now();

            // Create invoice from offer
            $invoice = new Invoice;
            $invoice->client_id = $offer->client_id;
            $invoice->project_id = $offer->project_id;
            $invoice->price_offer_id = $offer->id;
            $invoice->document_type = $request->get('document_type');
            $invoice->subject = $offer->title;
            $invoice->note = $offer->note;
            $invoice->status = 'open';
            $invoice->currency_id = $offer->currency_id;
            $invoice->subtotal = $offer->total_without_vat;
            $invoice->total_vat = $offer->total_vat;
            $invoice->total = $offer->total_with_vat;
            $invoice->issued_on = now()->toDateString();
            $invoice->due_on = now()->addDays(14)->toDateString();
            $invoice->local_updated_at = now();
            $invoice->save();

            // Copy items to invoice
            foreach ($offer->items as $offerItem) {
                $invoice->items()->create([
                    'name' => $offerItem->name,
                    'quantity' => $offerItem->quantity,
                    'unit_name' => $offerItem->unit_name,
                    'unit_price' => $offerItem->unit_price_without_vat,
                    'vat_rate' => $offerItem->vat_rate,
                    'total_without_vat' => $offerItem->total_without_vat,
                    'total_vat' => $offerItem->total_vat,
                    'total_with_vat' => $offerItem->total_with_vat,
                    'position' => $offerItem->position,
                ]);
            }

            $offer->invoice_id = $invoice->id;
            $offer->save();

            DB::commit();

            // Sync invoice to Fakturoid
            try {
                SyncInvoiceToFakturoidJob::dispatch($invoice->id);
            } catch (\Throwable $e) {
                // Fakturoid sync is optional
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při přijímání nabídky.'], 500);
        }

        return Response::json([
            'offer' => PriceOfferResource::make($offer->fresh(['items', 'client'])),
            'invoice' => InvoiceResource::make($invoice->fresh(['items', 'client'])),
        ]);
    }

    public function reject(int $id): JsonResponse
    {
        $offer = PriceOffer::find($id);
        if (! $offer) {
            App::abort(404);
        }

        $offer->status = 'rejected';
        $offer->rejected_at = now();
        $offer->save();

        return Response::json(PriceOfferResource::make($offer->fresh(['items', 'client'])));
    }

    public function downloadFile(int $offerId, int $fileId)
    {
        return $this->handleDownloadFile(PriceOffer::class, $offerId, $fileId);
    }

    public function pdf(int $id)
    {
        $offer = PriceOffer::with(['items', 'client'])->find($id);
        if (! $offer) {
            App::abort(404);
        }

        $pdf = Pdf::loadView('pdf.price-offer', [
            'offer' => $offer,
        ]);

        return $pdf->download('cenova-nabidka-'.$offer->code.'.pdf');
    }

    protected function generatePdf(PriceOffer $offer): void
    {
        try {
            $siteId = $offer->sites->first()?->id;
            $site = $siteId ? Site::find($siteId) : null;

            $pdf = Pdf::loadView('pdf.price-offer', [
                'offer' => $offer,
                'site' => $site,
            ]);

            $fileSlug = $offer->code ? preg_replace('/[^a-zA-Z0-9\-]/', '-', $offer->code) : $offer->id;
            $path = 'files/price-offers/'.$fileSlug.'.pdf';
            $name = 'cenova-nabidka-'.($offer->code ?? $offer->id).'.pdf';

            // Remove old generated PDF
            $existingFiles = $offer->files();
            foreach ($existingFiles as $file) {
                if ($file->mime_type === 'application/pdf') {
                    $offer->removeFile($file->id);
                }
            }

            $offer->attachFileFromContent($pdf->output(), $path, $name, 'application/pdf');
        } catch (\Throwable $e) {
            Log::warning('Price offer PDF generation failed: '.$e->getMessage());
        }
    }
}
