<?php

namespace App\Http\Controllers\Admin\Demand;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Demand\DemandResource;
use App\Models\Demand\Demand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DemandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Demand::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('fullname', 'like', '%' . $searchString . '%')
                    ->orWhere('email', 'like', '%' . $searchString . '%')
                    ->orWhere('url', 'like', '%' . $searchString . '%')
                    ->orWhere('offer_price', 'like', '%' . $searchString . '%')
                    ->orWhere('phone', '=', $searchString);
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $demands = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => DemandResource::collection($demands->items()),
                'total' => $demands->total(),
                'perPage' => $demands->perPage(),
                'currentPage' => $demands->currentPage(),
                'lastPage' => $demands->lastPage(),
            ]);
        }

        $demands = $query->get();
        return Response::json(DemandResource::collection($demands));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $demand = Demand::find($id);
            if (!$demand) {
                App::abort(404);
            }
        } else {
            $demand = new Demand();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $demand->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $demand->translateOrNew($locale)->fill($translation);
            }
            $demand->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating service.'], 500);
        }

        return Response::json(DemandResource::make($demand));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $demand = Demand::find($id);
        if (!$demand) {
            App::abort(404);
        }

        return Response::json(DemandResource::make($demand));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $demand = Demand::find($id);
        if (!$demand) {
            App::abort(404);
        }

        $demand->delete();
        return Response::json();
    }
}
