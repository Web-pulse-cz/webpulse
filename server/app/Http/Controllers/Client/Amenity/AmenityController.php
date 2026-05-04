<?php

namespace App\Http\Controllers\Client\Amenity;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Amenity\AmenityResource;
use App\Models\Amenity\Amenity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AmenityController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $amenities = Amenity::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('position', 'asc')
            ->get();

        return Response::json(AmenityResource::collection($amenities));
    }
}
