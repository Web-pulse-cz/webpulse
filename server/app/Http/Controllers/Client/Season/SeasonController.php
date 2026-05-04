<?php

namespace App\Http\Controllers\Client\Season;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Season\SeasonResource;
use App\Models\Season\Season;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SeasonController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $seasons = Season::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('position', 'asc')
            ->get();

        return Response::json(SeasonResource::collection($seasons));
    }
}
