<?php

namespace App\Http\Controllers\Client\Food\Foodstuff;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Food\Foodstuff\FoodstuffResource;
use App\Models\Food\Foodstuff\Foodstuff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class FoodstuffController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Foodstuff::query()
            ->with(['allergens', 'categories'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('id', 'desc');

        if ($request->filled('category_id')) {
            $categoryId = $request->get('category_id');
            $query->whereHas('categories', fn ($q) => $q->where('foodstuff_categories.id', $categoryId));
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => FoodstuffResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(FoodstuffResource::collection($query->get()));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $foodstuff = Foodstuff::query()
            ->with(['allergens', 'categories'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $foodstuff) {
            App::abort(404);
        }

        return Response::json(FoodstuffResource::make($foodstuff));
    }
}
