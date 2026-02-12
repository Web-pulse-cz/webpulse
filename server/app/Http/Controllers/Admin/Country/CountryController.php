<?php

namespace App\Http\Controllers\Admin\Country;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Country\CountryResource;
use App\Models\Country\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Country::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('code', '=', $searchString)
                    ->orWhere('iso', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('name', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $countries = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => CountryResource::collection($countries->items()),
                'total' => $countries->total(),
                'perPage' => $countries->perPage(),
                'currentPage' => $countries->currentPage(),
                'lastPage' => $countries->lastPage(),
            ]);
        }

        $countries = $query->get();
        return Response::json(CountryResource::collection($countries));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $country = Country::find($id);
            if (!$country) {
                App::abort(404);
            }
        } else {
            $country = new Country();
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $country->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $country->translateOrNew($locale)->fill($translation);
            }
            $country->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating country.'], 500);
        }

        return Response::json(CountryResource::make($country));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $country = Country::find($id);
        if (!$country) {
            App::abort(404);
        }

        return Response::json(CountryResource::make($country));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $country = Country::find($id);
        if (!$country) {
            App::abort(404);
        }

        $country->delete();
        return Response::json();
    }
}
