<?php

namespace App\Http\Controllers\Client\Food\Meal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Food\Meal\MealResource;
use App\Models\Food\Meal\Meal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class MealController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Meal::query()
            ->with(['allergens', 'foodstuffs', 'categories'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('id', 'desc');

        if ($request->filled('category_id')) {
            $categoryId = $request->get('category_id');
            $query->whereHas('categories', fn ($q) => $q->where('meal_categories.id', $categoryId));
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => MealResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(MealResource::collection($query->get()));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $meal = Meal::query()
            ->with(['allergens', 'foodstuffs', 'categories', 'recipe.foodstuffs', 'recipe.allergens'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $meal) {
            App::abort(404);
        }

        return Response::json(MealResource::make($meal));
    }
}
