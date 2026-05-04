<?php

namespace App\Http\Controllers\Client\Food\Recipe;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Food\Recipe\RecipeResource;
use App\Models\Food\Recipe\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class RecipeController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Recipe::query()
            ->with(['allergens', 'foodstuffs', 'categories'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('id', 'desc');

        if ($request->filled('category_id')) {
            $categoryId = $request->get('category_id');
            $query->whereHas('categories', fn ($q) => $q->where('recipe_categories.id', $categoryId));
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->get('difficulty'));
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => RecipeResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(RecipeResource::collection($query->get()));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $recipe = Recipe::query()
            ->with(['allergens', 'foodstuffs', 'categories'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $recipe) {
            App::abort(404);
        }

        return Response::json(RecipeResource::make($recipe));
    }
}
