<?php

namespace App\Http\Controllers\Admin\Food\Meal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Meal\MealResource;
use App\Models\Food\Meal\Meal;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MealController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = Meal::query()
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
            $meals = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => MealResource::collection($meals->items()),
                'total' => $meals->total(),
                'perPage' => $meals->perPage(),
                'currentPage' => $meals->currentPage(),
                'lastPage' => $meals->lastPage(),
            ]);
        }

        $meals = $query->get();

        return Response::json(MealResource::collection($meals));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $meal = Meal::find($id);
            if (! $meal) {
                App::abort(404);
            }
        } else {
            $meal = new Meal;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'allergens' => 'nullable|array',
            'categories' => 'nullable|array',
            'recipe_id' => 'nullable|integer|exists:recipes,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $meal->translateOrNew($locale)->fill($translation);
            }

            $meal->fill($request->only(['price', 'weight', 'recipe_id']));
            $meal->save();

            $meal->allergens()->sync($request->get('allergens', []));
            $meal->categories()->sync($request->get('categories', []));

            $meal->saveSites($meal, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while saving meal.'], 500);
        }

        return Response::json(MealResource::make($meal));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $meal = Meal::with(['recipe.foodstuffs', 'recipe.allergens'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $meal) {
            App::abort(404);
        }

        return Response::json(MealResource::make($meal));
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $meal = Meal::find($id);
        if (! $meal) {
            App::abort(404);
        }

        $meal->delete();

        return Response::json();
    }
}
