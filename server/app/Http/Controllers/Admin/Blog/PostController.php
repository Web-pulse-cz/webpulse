<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Blog\PostResource;
use App\Models\Blog\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = Post::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%')
                    ->orWhereTranslation($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('status', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('name', 'like', '%' . $searchString . '%')
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
            $posts = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => PostResource::collection($posts->items()),
                'total' => $posts->total(),
                'perPage' => $posts->perPage(),
                'currentPage' => $posts->currentPage(),
                'lastPage' => $posts->lastPage(),
            ]);
        }

        $posts = $query->get();
        return Response::json(PostResource::collection($posts));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $post = Post::find($id);
            if (!$post) {
                App::abort(404);
            }
        } else {
            $post = new Post();
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
            $post->fill($request->all());
            if ($request->get('published_from') == "") {
                $post->published_from = null;
            } else {
                $post->published_from = $request->get('published_from');
            }

            if ($request->get('published_to') == "") {
                $post->published_to = null;
            } else {
                $post->published_to = $request->get('published_to');
            }

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $post->translateOrNew($locale)->fill($translation);
            }
            $post->saveImages($post, $request->get('image'));
            $post->saveSites($post, $request->get('sites', []));

            $post->save();
            $post->categories()->sync($request->get('categories', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating post category.'], 500);
        }

        return Response::json(PostResource::make($post));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $post = Post::find($id);
        if (!$post) {
            App::abort(404);
        }

        return Response::json(PostResource::make($post));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $post = Post::find($id);
        if (!$post) {
            App::abort(404);
        }

        $post->delete();
        return Response::json();
    }
}
