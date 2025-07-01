<?php

namespace App\Http\Controllers\Client\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Blog\PostCategoryResource;
use App\Models\Blog\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PostCategoryController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $query = PostCategory::query()
            ->where('active', true)
            ->orderBy('position', 'asc');

        if ($request->has('paginate') && $request->get('paginate')) {
            $categories = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => PostCategoryResource::collection($categories->items()),
                'total' => $categories->total(),
                'perPage' => $categories->perPage(),
                'currentPage' => $categories->currentPage(),
                'lastPage' => $categories->lastPage(),
            ]);
        }

        $categories = $query->get();

        return Response::json(PostCategoryResource::collection($categories));
    }

    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        if (!$id) {
            return Response::json(['error' => 'Category ID is required'], 400);
        }

        $category = PostCategory::query()
            ->where('active', true)
            ->find($id);

        if (!$category) {
            return Response::json(['error' => 'Category not found'], 404);
        }

        return Response::json(PostCategoryResource::make($category));
    }
}
