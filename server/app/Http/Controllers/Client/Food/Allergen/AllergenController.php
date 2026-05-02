<?php

namespace App\Http\Controllers\Client\Food\Allergen;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Food\Allergen\AllergenResource;
use App\Models\Food\Allergen\Allergen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AllergenController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $allergens = Allergen::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('number', 'asc')
            ->get();

        return Response::json(AllergenResource::collection($allergens));
    }
}
