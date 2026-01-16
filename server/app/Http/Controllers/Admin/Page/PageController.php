<?php

namespace App\Http\Controllers\Admin\Page;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Page\PageResource;
use App\Models\Page\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Page::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%')
                    ->orWhereTranslation($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->whereTranslation('name', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('slug', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('perex', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('description', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('meta_title', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('meta_description', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $pages = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => PageResource::collection($pages->items()),
                'total' => $pages->total(),
                'perPage' => $pages->perPage(),
                'currentPage' => $pages->currentPage(),
                'lastPage' => $pages->lastPage(),
            ]);
        }

        $pages = $query->get();
        return Response::json(PageResource::collection($pages));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $page = Page::find($id);
            if (!$page) {
                App::abort(404);
            }
        } else {
            $page = new Page();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $page->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $page->translateOrNew($locale)->fill($translation);
            }

            $page->save();

            $page->saveSites($page, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating post category.'], 500);
        }

        return Response::json(PageResource::make($page));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $page = Page::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (!$page) {
            App::abort(404);
        }

        return Response::json(PageResource::make($page));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $page = Page::find($id);
        if (!$page) {
            App::abort(404);
        }

        $page->delete();
        return Response::json();
    }
}
