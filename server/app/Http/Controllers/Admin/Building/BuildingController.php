<?php

namespace App\Http\Controllers\Admin\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Building\BuildingResource;
use App\Models\Building\Building;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class BuildingController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Building::query()
            ->withCount('apartments')
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('address_city', 'like', '%'.$search.'%')
                    ->orWhere('address_street', 'like', '%'.$search.'%')
                    ->orWhere('contact_name', 'like', '%'.$search.'%')
                    ->orWhereTranslation('name', 'like', '%'.$search.'%');
            });
        }

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('position');
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => BuildingResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(BuildingResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $building = Building::withCount('apartments')
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $building) {
            App::abort(404);
        }

        return Response::json(BuildingResource::make($building));
    }

    public function store(Request $request, ?int $id = null)
    {
        if ($id) {
            $building = Building::find($id);
            if (! $building) {
                App::abort(404);
            }
        } else {
            $building = new Building;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $building->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $building->translateOrNew($locale)->fill($translation);
            }

            $building->save();

            $building->saveImages($building, $request->get('image'));
            $building->saveSites($building, $request->get('sites', []));

            DB::commit();

            return Response::json(BuildingResource::make($building->fresh()));
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while saving the building.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $building = Building::find($id);
        if (! $building) {
            App::abort(404);
        }

        $building->delete();

        return Response::json();
    }
}
