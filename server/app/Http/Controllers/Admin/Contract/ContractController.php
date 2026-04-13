<?php

namespace App\Http\Controllers\Admin\Contract;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Contract\ContractResource;
use App\Models\Contract\Contract;
use App\Traits\Siteable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    use Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Contract::with(['employee', 'project'])
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('employee', fn($e) => $e->where('first_name', 'like', '%' . $search . '%')->orWhere('last_name', 'like', '%' . $search . '%'))
                    ->orWhereHas('project', fn($p) => $p->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->get('employee_id'));
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
            $contracts = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ContractResource::collection($contracts->items()),
                'total' => $contracts->total(),
                'perPage' => $contracts->perPage(),
                'currentPage' => $contracts->currentPage(),
                'lastPage' => $contracts->lastPage(),
            ]);
        }

        return Response::json(ContractResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $contract = Contract::find($id);
            if (!$contract) {
                App::abort(404);
            }
        } else {
            $contract = new Contract;
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'project_id' => 'nullable|integer|exists:projects,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|integer|exists:currencies,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $contract->fill($request->except(['file', 'sites']));
            $contract->save();

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $contract->attachUploadedFile($file, 'files/contracts/' . $contract->id);
            }

            if ($request->has('sites')) {
                $this->saveSites($contract, $request->get('sites', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání smlouvy: ' . $e->getMessage()], 500);
        }

        // Generate PDF from content if contract has content
        if ($contract->content) {
            $this->generatePdf($contract->fresh(['employee', 'project', 'sites']));
        }

        return Response::json(ContractResource::make($contract->fresh(['employee', 'project', 'sites'])));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $contract = Contract::with(['employee', 'project', 'sites'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (!$contract) {
            App::abort(404);
        }

        return Response::json(ContractResource::make($contract));
    }

    public function destroy(int $id): JsonResponse
    {
        $contract = Contract::find($id);
        if (!$contract) {
            App::abort(404);
        }

        $contract->removeAllFiles();
        $contract->delete();

        return Response::json();
    }

    public function downloadFile(int $contractId, int $fileId)
    {
        $contract = Contract::find($contractId);
        if (!$contract) {
            App::abort(404);
        }

        $file = DB::table('fileables')
            ->where('id', $fileId)
            ->where('fileable_id', $contractId)
            ->where('fileable_type', Contract::class)
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

    protected function generatePdf(Contract $contract): void
    {
        try {
            $siteId = $contract->sites->first()?->id;
            $site = $siteId ? \App\Models\Site\Site::find($siteId) : null;

            $pdf = Pdf::loadView('pdf.contract', [
                'contract' => $contract,
                'site' => $site,
            ]);

            $fileSlug = \Illuminate\Support\Str::slug($contract->title) ?: $contract->id;
            $path = 'files/contracts/' . $contract->id . '/' . $fileSlug . '.pdf';
            $name = 'smlouva-' . $fileSlug . '.pdf';

            // Remove old generated PDF
            $existingFiles = $contract->files();
            foreach ($existingFiles as $file) {
                if ($file->mime_type === 'application/pdf' && str_starts_with($file->path, 'files/contracts/')) {
                    $contract->removeFile($file->id);
                }
            }

            $contract->attachFileFromContent($pdf->output(), $path, $name, 'application/pdf');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Contract PDF generation failed: ' . $e->getMessage());
        }
    }
}
