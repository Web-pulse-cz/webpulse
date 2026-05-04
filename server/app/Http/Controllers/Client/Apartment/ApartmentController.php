<?php

namespace App\Http\Controllers\Client\Apartment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Apartment\ApartmentResource;
use App\Models\Apartment\Apartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class ApartmentController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Apartment::query()
            ->with(['type', 'building', 'currency', 'amenities', 'seasonPrices'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('status', '!=', 'draft')
            ->orderBy('position', 'asc');

        if ($request->filled('apartment_type_id')) {
            $query->where('apartment_type_id', $request->get('apartment_type_id'));
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->get('building_id'));
        }

        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', (int) $request->get('capacity'));
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

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $apartment = Apartment::query()
            ->with(['type', 'building', 'currency', 'amenities', 'seasonPrices'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('status', '!=', 'draft')
            ->find($id);

        if (! $apartment) {
            App::abort(404);
        }

        return Response::json(ApartmentResource::make($apartment));
    }
}
