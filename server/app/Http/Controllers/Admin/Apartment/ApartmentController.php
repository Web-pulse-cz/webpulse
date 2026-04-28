<?php

namespace App\Http\Controllers\Admin\Apartment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Apartment\ApartmentResource;
use App\Models\Apartment\Apartment;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ApartmentController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Apartment::query()
            ->with(['type', 'building', 'currency', 'amenities'])
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%'.$search.'%')
                    ->orWhereTranslation('name', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('apartment_type_id')) {
            $query->where('apartment_type_id', $request->get('apartment_type_id'));
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->get('building_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('position');
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ApartmentResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(ApartmentResource::collection($query->get()));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $item = Apartment::with(['type', 'building', 'currency', 'amenities', 'seasonPrices'])
            ->withCount(['reservations', 'blocks'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $item) {
            App::abort(404);
        }

        return Response::json(ApartmentResource::make($item));
    }

    public function store(Request $request, ?int $id = null)
    {
        if ($id) {
            $item = Apartment::find($id);
            if (! $item) {
                App::abort(404);
            }
        } else {
            $item = new Apartment;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'apartment_type_id' => 'nullable|exists:apartment_types,id',
            'building_id' => 'nullable|exists:buildings,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $item->fill($request->all());
            if (! $id) {
                $item->generateCode();
            }

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $item->translateOrNew($locale)->fill($translation);
            }

            $item->save();

            $item->saveImages($item, $request->get('image'));
            $item->saveSites($item, $request->get('sites', []));

            if ($request->has('amenities')) {
                $item->amenities()->sync($request->get('amenities', []));
            }

            if ($request->has('season_prices') && is_array($request->get('season_prices'))) {
                $sync = [];
                foreach ($request->get('season_prices') as $entry) {
                    if (! isset($entry['season_id'])) {
                        continue;
                    }
                    $sync[(int) $entry['season_id']] = ['price' => (float) ($entry['price'] ?? 0)];
                }
                $item->seasons()->sync($sync);
            }

            DB::commit();

            return Response::json(ApartmentResource::make(
                $item->fresh(['type', 'building', 'currency', 'amenities', 'seasonPrices'])
            ));
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while saving the apartment.'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $item = Apartment::find($id);
        if (! $item) {
            App::abort(404);
        }

        $item->delete();

        return Response::json();
    }
}
