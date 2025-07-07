<?php

namespace App\Http\Controllers\Client\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Blog\PostResource;
use App\Models\Blog\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PostController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $query = Post::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_from')
                    ->orWhere('published_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('published_to')
                    ->orWhere('published_to', '>=', now());
            })
            ->with(['categories'])
            ->orderBy('published_from', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->has('categoryId') && $request->get('categoryId') !== null) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('posts_in_categories.id', $request->get('categoryId'));
            });
        }

        if ($request->has('paginate')) {
            $posts = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => PostResource::collection($posts->items()),
                'total' => $posts->total(),
                'perPage' => $posts->perPage(),
                'currentPage' => $posts->currentPage(),
                'lastPage' => $posts->lastPage(),
                'request' => $request->all(),
            ]);
        }

        $posts = $query->get();

        return Response::json(PostResource::collection($posts));
    }

    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        if (!$id) {
            return Response::json(['error' => 'Post ID is required'], 400);
        }

        $post = Post::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_from')
                    ->orWhere('published_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('published_to')
                    ->orWhere('published_to', '>=', now());
            })
            ->with(['categories'])
            ->find($id);

        if (!$post) {
            return Response::json(['error' => 'Post not found'], 404);
        }

        return Response::json(PostResource::make($post));
    }
}
