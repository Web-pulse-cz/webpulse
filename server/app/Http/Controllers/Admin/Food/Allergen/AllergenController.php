<?php

namespace App\Http\Controllers\Admin\Food\Allergen;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Allergen\AllergenResource;
use App\Models\Food\Allergen\Allergen;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AllergenController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService();
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = Allergen::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('rating', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('name', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('content', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $allergens = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => AllergenResource::collection($allergens->items()),
                'total' => $allergens->total(),
                'perPage' => $allergens->perPage(),
                'currentPage' => $allergens->currentPage(),
                'lastPage' => $allergens->lastPage(),
            ]);
        }

        $allergens = $query->get();
        return Response::json(AllergenResource::collection($allergens));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $allergen = Allergen::find($id);
            if (!$allergen) {
                App::abort(404);
            }
        } else {
            $allergen = new Allergen();
        }

        $validator = Validator::make($request->all(), [
            'number' => 'required|integer|min:0|max:30',
            'translations' => 'required|array'
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $allergen->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale, false);
                $allergen->translateOrNew($locale)->fill($translation);
            }

            $allergen->save();

            $allergen->saveSites($allergen, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating allergen.'], 500);
        }

        return Response::json(AllergenResource::make($allergen));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $allergen = Allergen::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (!$allergen) {
            App::abort(404);
        }

        return Response::json(AllergenResource::make($allergen));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $allergen = Allergen::find($id);
        if (!$allergen) {
            App::abort(404);
        }

        $allergen->delete();
        return Response::json();
    }
}
