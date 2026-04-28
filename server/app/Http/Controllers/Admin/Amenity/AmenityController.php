<?php

namespace App\Http\Controllers\Admin\Amenity;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Amenity\AmenityResource;
use App\Models\Amenity\Amenity;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AmenityController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Amenity::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $query->whereTranslation('name', 'like', '%'.$request->get('search').'%');
        }

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('position');
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => AmenityResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(AmenityResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $item = Amenity::whereRelation('sites', 'site_id', $siteId)->find($id);
        if (! $item) {
            App::abort(404);
        }

        return Response::json(AmenityResource::make($item));
    }

    public function store(Request $request, ?int $id = null)
    {
        if ($id) {
            $item = Amenity::find($id);
            if (! $item) {
                App::abort(404);
            }
        } else {
            $item = new Amenity;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $item->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale, false);
                $item->translateOrNew($locale)->fill($translation);
            }

            $item->save();
            $item->saveSites($item, $request->get('sites', []));

            DB::commit();

            return Response::json(AmenityResource::make($item->fresh()));
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while saving the amenity.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $item = Amenity::find($id);
        if (! $item) {
            App::abort(404);
        }

        $item->delete();

        return Response::json();
    }
}
