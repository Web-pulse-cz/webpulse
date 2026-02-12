<?php

namespace App\Http\Controllers\Admin\TaxRate;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TaxRate\TaxRateResource;
use App\Models\TaxRate\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TaxRateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxRate::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('name', 'like', '%' . $searchString . '%')
                    ->orWhere('rate', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $taxRates = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => TaxRateResource::collection($taxRates->items()),
                'total' => $taxRates->total(),
                'perPage' => $taxRates->perPage(),
                'currentPage' => $taxRates->currentPage(),
                'lastPage' => $taxRates->lastPage(),
            ]);
        }

        $taxRates = $query->get();
        return Response::json(TaxRateResource::collection($taxRates));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $taxRate = TaxRate::find($id);
            if (!$taxRate) {
                App::abort(404);
            }
        } else {
            $taxRate = new TaxRate();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'rate' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $taxRate->fill($request->all());
            $taxRate->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating tax rate.'], 500);
        }

        return Response::json(TaxRateResource::make($taxRate));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $taxRate = TaxRate::find($id);
        if (!$taxRate) {
            App::abort(404);
        }

        return Response::json(TaxRateResource::make($taxRate));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $taxRate = TaxRate::find($id);
        if (!$taxRate) {
            App::abort(404);
        }

        $taxRate->delete();
        return Response::json();
    }
}
