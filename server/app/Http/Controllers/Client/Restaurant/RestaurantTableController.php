<?php

namespace App\Http\Controllers\Client\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Restaurant\RestaurantTableResource;
use App\Models\Restaurant\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class RestaurantTableController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = RestaurantTable::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('position', 'asc');

        if ($request->filled('seats')) {
            $query->where('seats', '>=', (int) $request->get('seats'));
        }

        return Response::json(RestaurantTableResource::collection($query->get()));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $table = RestaurantTable::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $table) {
            App::abort(404);
        }

        return Response::json(RestaurantTableResource::make($table));
    }
}
