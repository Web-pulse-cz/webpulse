<?php

namespace App\Http\Controllers\Admin\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Voucher\VoucherResource;
use App\Models\Voucher\Voucher;
use App\Traits\Siteable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    use Siteable;

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = Voucher::whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->has('paginate')) {
            $vouchers = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => VoucherResource::collection($vouchers->items()),
                'total' => $vouchers->total(),
                'perPage' => $vouchers->perPage(),
                'currentPage' => $vouchers->currentPage(),
                'lastPage' => $vouchers->lastPage(),
            ]);
        }

        return Response::json(VoucherResource::collection($query->get()));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $voucher = Voucher::find($id);
            if (! $voucher) {
                App::abort(404);
            }
        } else {
            $voucher = new Voucher;
        }

        $validator = Validator::make($request->all(), [
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'currency_id' => 'nullable|integer|exists:currencies,id',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $voucher->fill($request->except(['customers', 'sites']));

            // Auto-generate code if empty
            if (! $voucher->code) {
                $voucher->code = strtoupper(Str::random(8));
            }

            $voucher->save();

            // Sync customers
            if ($request->has('customers')) {
                $voucher->customers()->sync($request->get('customers', []));
            }

            if ($request->has('sites')) {
                $this->saveSites($voucher, $request->get('sites', []));
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání voucheru.'], 500);
        }

        return Response::json(VoucherResource::make(
            $voucher->fresh(['customers', 'sites'])
        ));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $voucher = Voucher::with(['customers', 'sites'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $voucher) {
            App::abort(404);
        }

        return Response::json(VoucherResource::make($voucher));
    }

    public function destroy(int $id): JsonResponse
    {
        $voucher = Voucher::find($id);
        if (! $voucher) {
            App::abort(404);
        }

        $voucher->customers()->detach();
        $voucher->delete();

        return Response::json();
    }
}
