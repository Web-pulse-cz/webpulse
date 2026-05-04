<?php

namespace App\Http\Controllers\Client\Apartment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Apartment\ApartmentTypeResource;
use App\Models\Apartment\ApartmentType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class ApartmentTypeController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = ApartmentType::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('position', 'asc');

        return Response::json(ApartmentTypeResource::collection($query->get()));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $type = ApartmentType::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $type) {
            App::abort(404);
        }

        return Response::json(ApartmentTypeResource::make($type));
    }
}
