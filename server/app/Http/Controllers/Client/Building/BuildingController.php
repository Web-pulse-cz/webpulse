<?php

namespace App\Http\Controllers\Client\Building;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Building\BuildingResource;
use App\Models\Building\Building;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class BuildingController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Building::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('position', 'asc');

        return Response::json(BuildingResource::collection($query->get()));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $building = Building::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $building) {
            App::abort(404);
        }

        return Response::json(BuildingResource::make($building));
    }
}
