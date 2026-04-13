<?php

namespace App\Http\Controllers\Admin\Food\Recipe;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Recipe\RecipeResource;
use App\Models\Food\Recipe\Recipe;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = Recipe::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->orWhereTranslation('name', 'like', '%'.$searchString.'%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $recipes = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => RecipeResource::collection($recipes->items()),
                'total' => $recipes->total(),
                'perPage' => $recipes->perPage(),
                'currentPage' => $recipes->currentPage(),
                'lastPage' => $recipes->lastPage(),
            ]);
        }

        $recipes = $query->get();

        return Response::json(RecipeResource::collection($recipes));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $recipe = Recipe::find($id);
            if (! $recipe) {
                App::abort(404);
            }
        } else {
            $recipe = new Recipe;
        }

        $validator = Validator::make($request->all(), [
            'difficulty' => 'nullable|in:easy,medium,hard',
            'time_to_prepare' => 'nullable|integer|min:0',
            'translations' => 'required|array',
            'allergens' => 'nullable|array',
            'allergens.*' => 'integer|exists:allergens,id',
            'foodstuffs' => 'nullable|array',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:recipe_categories,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $recipe->fill($request->only('difficulty', 'time_to_prepare'));

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $recipe->translateOrNew($locale)->fill($translation);
            }

            $recipe->save();

            $recipe->allergens()->sync($request->get('allergens', []));
            $recipe->categories()->sync($request->get('categories', []));

            $foodstuffSync = [];
            foreach ($request->get('foodstuffs', []) as $foodstuff) {
                if (! empty($foodstuff['id'])) {
                    $foodstuffSync[$foodstuff['id']] = [
                        'quantity' => $foodstuff['quantity'] ?? null,
                        'unit' => $foodstuff['unit'] ?? null,
                    ];
                }
            }
            $recipe->foodstuffs()->sync($foodstuffSync);

            $recipe->saveSites($recipe, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while saving recipe.'], 500);
        }

        return Response::json(RecipeResource::make($recipe));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $recipe = Recipe::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $recipe) {
            App::abort(404);
        }

        return Response::json(RecipeResource::make($recipe));
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $recipe = Recipe::find($id);
        if (! $recipe) {
            App::abort(404);
        }

        $recipe->delete();

        return Response::json();
    }
}
