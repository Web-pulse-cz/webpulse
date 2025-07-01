<?php

namespace App\Http\Controllers\Client\Review;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Review\ReviewResource;
use App\Models\Review\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReviewController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $query = Review::query()
            ->where('active', true)
            ->orderBy('created_at', 'desc');

        if ($request->has('paginate')) {
            $reviews = $query->paginate($request->get('paginate'));

            return response()->json([
                'data' => ReviewResource::collection($reviews->items()),
                'total' => $reviews->total(),
                'perPage' => $reviews->perPage(),
                'currentPage' => $reviews->currentPage(),
                'lastPage' => $reviews->lastPage(),
            ]);
        }

        $reviews = $query->get();

        return Response::json(ReviewResource::collection($reviews));
    }
}
