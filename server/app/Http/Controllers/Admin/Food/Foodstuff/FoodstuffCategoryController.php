<?php

namespace App\Http\Controllers\Admin\Food\Foodstuff;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Foodstuff\FoodstuffCategoryResource;
use App\Models\Food\Foodstuff\FoodstuffCategory;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class FoodstuffCategoryController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = FoodstuffCategory::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->where('rating', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('name', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('content', 'like', '%'.$searchString.'%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $foodstuffCategorys = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => FoodstuffCategoryResource::collection($foodstuffCategorys->items()),
                'total' => $foodstuffCategorys->total(),
                'perPage' => $foodstuffCategorys->perPage(),
                'currentPage' => $foodstuffCategorys->currentPage(),
                'lastPage' => $foodstuffCategorys->lastPage(),
            ]);
        }

        $foodstuffCategorys = $query->get();

        return Response::json(FoodstuffCategoryResource::collection($foodstuffCategorys));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $foodstuffCategory = FoodstuffCategory::find($id);
            if (! $foodstuffCategory) {
                App::abort(404);
            }
        } else {
            $foodstuffCategory = new FoodstuffCategory;
        }

        $validator = Validator::make($request->all(), [
            'foodstuff_category_id' => 'nullable|integer|exists:foodstuff_categories,id',
            'translations' => 'required|array',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $foodstuffCategory->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $foodstuffCategory->translateOrNew($locale)->fill($translation);
            }

            $foodstuffCategory->save();

            $foodstuffCategory->saveSites($foodstuffCategory, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while updating foodstuff category.'], 500);
        }

        return Response::json(FoodstuffCategoryResource::make($foodstuffCategory));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $foodstuffCategory = FoodstuffCategory::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $foodstuffCategory) {
            App::abort(404);
        }

        return Response::json(FoodstuffCategoryResource::make($foodstuffCategory));
    }

    public function destroy(int $id)
    {
        if (! $id) {
            App::abort(400);
        }

        $foodstuffCategory = FoodstuffCategory::find($id);
        if (! $foodstuffCategory) {
            App::abort(404);
        }

        $foodstuffCategory->delete();

        return Response::json();
    }
}
