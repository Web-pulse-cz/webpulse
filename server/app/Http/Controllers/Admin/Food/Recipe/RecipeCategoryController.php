<?php

namespace App\Http\Controllers\Admin\Food\Recipe;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Recipe\RecipeCategoryResource;
use App\Models\Food\Recipe\RecipeCategory;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class RecipeCategoryController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService();
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = RecipeCategory::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->orWhereTranslation('name', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $categories = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => RecipeCategoryResource::collection($categories->items()),
                'total' => $categories->total(),
                'perPage' => $categories->perPage(),
                'currentPage' => $categories->currentPage(),
                'lastPage' => $categories->lastPage(),
            ]);
        }

        $categories = $query->get();
        return Response::json(RecipeCategoryResource::collection($categories));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $category = RecipeCategory::find($id);
            if (!$category) {
                App::abort(404);
            }
        } else {
            $category = new RecipeCategory();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $category->translateOrNew($locale)->fill($translation);
            }

            $category->save();

            $category->saveSites($category, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while saving recipe category.'], 500);
        }

        return Response::json(RecipeCategoryResource::make($category));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $category = RecipeCategory::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (!$category) {
            App::abort(404);
        }

        return Response::json(RecipeCategoryResource::make($category));
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $category = RecipeCategory::find($id);
        if (!$category) {
            App::abort(404);
        }

        $category->delete();
        return Response::json();
    }
}
