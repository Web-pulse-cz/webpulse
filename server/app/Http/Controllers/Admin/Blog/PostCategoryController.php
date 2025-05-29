<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Blog\PostCategoryResource;
use App\Models\Blog\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PostCategory::query();

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
            $postCategorys = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => PostCategoryResource::collection($postCategorys->items()),
                'total' => $postCategorys->total(),
                'perPage' => $postCategorys->perPage(),
                'currentPage' => $postCategorys->currentPage(),
                'lastPage' => $postCategorys->lastPage(),
            ]);
        }

        $postCategorys = $query->get();
        return Response::json(PostCategoryResource::collection($postCategorys));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $postCategory = PostCategory::find($id);
            if (!$postCategory) {
                App::abort(404);
            }
        } else {
            $postCategory = new PostCategory();
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
            $postCategory->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $postCategory->translateOrNew($locale)->fill($translation);
            }
            $postCategory->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating post category.'], 500);
        }

        return Response::json(PostCategoryResource::make($postCategory));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $postCategory = PostCategory::find($id);
        if (!$postCategory) {
            App::abort(404);
        }

        return Response::json(PostCategoryResource::make($postCategory));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $postCategory = PostCategory::find($id);
        if (!$postCategory) {
            App::abort(404);
        }

        $postCategory->delete();
        return Response::json();
    }
}
