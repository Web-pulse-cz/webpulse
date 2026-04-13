<?php

namespace App\Http\Controllers\Admin\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Invoice\InvoiceResource;
use App\Http\Resources\Admin\Invoice\InvoiceSimpleResource;
use App\Jobs\Fakturoid\SyncInvoiceToFakturoidJob;
use App\Models\Invoice\Invoice;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    use Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Invoice::with('client')
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('variable_symbol', 'like', '%'.$search.'%');
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

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('issued_on', 'desc');
        }

        if ($request->has('paginate')) {
            $invoices = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => InvoiceSimpleResource::collection($invoices->items()),
                'total' => $invoices->total(),
                'perPage' => $invoices->perPage(),
                'currentPage' => $invoices->currentPage(),
                'lastPage' => $invoices->lastPage(),
            ]);
        }

        return Response::json(InvoiceSimpleResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $invoice = Invoice::find($id);
            if (! $invoice) {
                App::abort(404);
            }
        } else {
            $invoice = new Invoice;
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'nullable|integer|exists:clients,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'language_id' => 'nullable|integer|exists:languages,id',
            'issued_on' => 'nullable|date',
            'due_on' => 'nullable|date',
            'taxable_fulfillment_due' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $invoice->fill($request->except(['items']));
            $invoice->local_updated_at = now();

            // Calculate totals from items
            $items = $request->get('items', []);
            $subtotal = 0;
            $totalVat = 0;
            $total = 0;

            foreach ($items as $item) {
                $itemTotal = ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
                $itemVat = $itemTotal * (($item['vat_rate'] ?? 0) / 100);
                $subtotal += $itemTotal;
                $totalVat += $itemVat;
                $total += $itemTotal + $itemVat;
            }

            $invoice->subtotal = $subtotal;
            $invoice->total_vat = $totalVat;
            $invoice->total = $total;
            $invoice->save();

            // Sync items
            $invoice->items()->delete();
            $position = 0;
            foreach ($items as $item) {
                $unitPrice = $item['unit_price'] ?? 0;
                $qty = $item['quantity'] ?? 1;
                $vatRate = $item['vat_rate'] ?? 0;
                $totalWithoutVat = $qty * $unitPrice;
                $itemVat = $totalWithoutVat * ($vatRate / 100);

                $invoice->items()->create([
                    'name' => $item['name'] ?? '',
                    'quantity' => $qty,
                    'unit_name' => $item['unit_name'] ?? null,
                    'unit_price' => $unitPrice,
                    'vat_rate' => $vatRate,
                    'total_without_vat' => $totalWithoutVat,
                    'total_vat' => $itemVat,
                    'total_with_vat' => $totalWithoutVat + $itemVat,
                    'position' => $position++,
                ]);
            }

            if ($request->has('sites')) {
                $this->saveSites($invoice, $request->get('sites', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Invoice save error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return Response::json(['message' => 'Chyba při ukládání faktury: ' . $e->getMessage()], 500);
        }

        // Generate PDF: Fakturoid if available, otherwise local
        try {
            $siteId = $this->handleSite($request->header('X-Site-Hash'));
            $site = \App\Models\Site\Site::find($siteId);

            if ($site?->hasFakturoid()) {
                SyncInvoiceToFakturoidJob::dispatch($invoice->id, $siteId);
            } else {
                $this->generateLocalPdf($invoice->fresh(['items', 'client', 'sites']), $site);
            }
        } catch (\Throwable $e) {
            // Site hash missing — try local PDF generation without site
            $this->generateLocalPdf($invoice->fresh(['items', 'client', 'sites']), null);
        }

        return Response::json(InvoiceResource::make($invoice->fresh(['items', 'client', 'sites'])));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $invoice = Invoice::with(['items', 'client', 'sites'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $invoice) {
            App::abort(404);
        }

        return Response::json(InvoiceResource::make($invoice));
    }

    public function destroy(int $id): JsonResponse
    {
        $invoice = Invoice::find($id);
        if (! $invoice) {
            App::abort(404);
        }

        $invoice->removeAllFiles();
        $invoice->items()->delete();
        $invoice->delete();

        return Response::json();
    }

    public function downloadFile(int $invoiceId, int $fileId)
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            App::abort(404);
        }

        $file = \Illuminate\Support\Facades\DB::table('fileables')
            ->where('id', $fileId)
            ->where('fileable_id', $invoiceId)
            ->where('fileable_type', Invoice::class)
            ->first();

        if (!$file) {
            App::abort(404);
        }

        $path = storage_path('app/public/' . $file->path);
        if (!file_exists($path)) {
            App::abort(404);
        }

        return response()->download($path, $file->name, [
            'Content-Type' => $file->mime_type,
        ]);
    }

    protected function generateLocalPdf(Invoice $invoice, ?\App\Models\Site\Site $site): void
    {
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'site' => $site,
            ]);

            $fileSlug = $invoice->number ? preg_replace('/[^a-zA-Z0-9\-]/', '-', $invoice->number) : $invoice->id;
            $path = 'files/invoices/' . $fileSlug . '.pdf';
            $name = 'faktura-' . ($invoice->number ?? $invoice->id) . '.pdf';

            // Remove old PDF
            $existingFiles = $invoice->files();
            foreach ($existingFiles as $file) {
                if ($file->mime_type === 'application/pdf') {
                    $invoice->removeFile($file->id);
                }
            }

            $invoice->attachFileFromContent($pdf->output(), $path, $name, 'application/pdf');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Local PDF generation failed: ' . $e->getMessage());
        }
    }
}
