<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Faq\FaqCategoryResource;
use App\Models\Faq\FaqCategory;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FaqCategoryController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService();
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = FaqCategory::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%')
                    ->orWhereTranslation($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->whereTranslation('name', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $faqCategories = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => FaqCategoryResource::collection($faqCategories->items()),
                'total' => $faqCategories->total(),
                'perPage' => $faqCategories->perPage(),
                'currentPage' => $faqCategories->currentPage(),
                'lastPage' => $faqCategories->lastPage(),
            ]);
        }

        $faqCategories = $query->get();
        return Response::json(FaqCategoryResource::collection($faqCategories));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $faqCategory = FaqCategory::find($id);
            if (!$faqCategory) {
                App::abort(404);
            }
        } else {
            $faqCategory = new FaqCategory();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array'
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $faqCategory->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                if ($locale != 'cs') {
                    foreach ($translation as $key => $value) {
                        if (in_array($value, ['', null])) {
                            $value = $request->translations['cs'][$key];
                        }
                        $value = $this->googleTranslatorService->translate($value, $locale);
                        $translation[$key] = $value;
                    }
                }
                $faqCategory->translateOrNew($locale)->fill($translation);
            }


            $faqCategory->save();
            $faqCategory->saveSites($faqCategory, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating faq category.'], 500);
        }

        return Response::json(FaqCategoryResource::make($faqCategory));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $faqCategory = FaqCategory::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (!$faqCategory) {
            App::abort(404);
        }

        return Response::json(FaqCategoryResource::make($faqCategory));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $faqCategory = FaqCategory::find($id);
        if (!$faqCategory) {
            App::abort(404);
        }

        $faqCategory->delete();
        return Response::json();
    }
}
