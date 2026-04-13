<?php

namespace App\Http\Controllers\Admin\Currency;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Currency\CurrencyListResource;
use App\Http\Resources\Admin\Currency\CurrencyResource;
use App\Models\Currency\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Currency::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->where('code', '=', $searchString)
                    ->orWhere('rate', 'like', '%'.$searchString.'%')
                    ->orWhere('decimals', 'like', '%'.$searchString.'%')
                    ->orWhere('bank_account_number', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('name', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('symbol_before', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('symbol_after', 'like', '%'.$searchString.'%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $currencys = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => CurrencyListResource::collection($currencys->items()),
                'total' => $currencys->total(),
                'perPage' => $currencys->perPage(),
                'currentPage' => $currencys->currentPage(),
                'lastPage' => $currencys->lastPage(),
            ]);
        }

        $currencys = $query->get();

        return Response::json(CurrencyListResource::collection($currencys));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $currency = Currency::find($id);
            if (! $currency) {
                App::abort(404);
            }
        } else {
            $currency = new Currency;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $currency->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $currency->translateOrNew($locale)->fill($translation);
            }
            $currency->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while updating language.'], 500);
        }

        return Response::json(CurrencyResource::make($currency));
    }

    public function show(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $currency = Currency::find($id);
        if (! $currency) {
            App::abort(404);
        }

        return Response::json(CurrencyResource::make($currency));
    }

    public function destroy(int $id)
    {
        if (! $id) {
            App::abort(400);
        }

        $currency = Currency::find($id);
        if (! $currency) {
            App::abort(404);
        }

        $currency->delete();

        return Response::json();
    }
}
