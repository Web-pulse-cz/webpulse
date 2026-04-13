<?php

namespace App\Http\Controllers\Admin\Food\Foodstuff;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Foodstuff\FoodstuffResource;
use App\Models\Food\Foodstuff\Foodstuff;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class FoodstuffController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = Foodstuff::query()
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
            $foodstuffs = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => FoodstuffResource::collection($foodstuffs->items()),
                'total' => $foodstuffs->total(),
                'perPage' => $foodstuffs->perPage(),
                'currentPage' => $foodstuffs->currentPage(),
                'lastPage' => $foodstuffs->lastPage(),
            ]);
        }

        $foodstuffs = $query->get();

        return Response::json(FoodstuffResource::collection($foodstuffs));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $foodstuff = Foodstuff::find($id);
            if (! $foodstuff) {
                App::abort(404);
            }
        } else {
            $foodstuff = new Foodstuff;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'allergens' => 'nullable|array',
            'allergens.*' => 'integer|exists:allergens,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:foodstuff_categories,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $foodstuff->fill($request->only('macronutrients'));

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $foodstuff->translateOrNew($locale)->fill($translation);
            }

            $foodstuff->save();

            $foodstuff->allergens()->sync($request->get('allergens', []));
            $foodstuff->categories()->sync($request->get('categories', []));

            $foodstuff->saveSites($foodstuff, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while saving foodstuff.'], 500);
        }

        return Response::json(FoodstuffResource::make($foodstuff));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $foodstuff = Foodstuff::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $foodstuff) {
            App::abort(404);
        }

        return Response::json(FoodstuffResource::make($foodstuff));
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $foodstuff = Foodstuff::find($id);
        if (! $foodstuff) {
            App::abort(404);
        }

        $foodstuff->delete();

        return Response::json();
    }
}
