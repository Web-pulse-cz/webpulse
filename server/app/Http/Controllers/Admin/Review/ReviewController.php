<?php

namespace App\Http\Controllers\Admin\Review;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Review\ReviewResource;
use App\Models\Review\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::query();

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
            $reviews = $query->paginate($request->get('paginate'));

            return Response::json([
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

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $review = Review::find($id);
            if (!$review) {
                App::abort(404);
            }
        } else {
            $review = new Review();
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:0|max:5',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
            'translations.*.content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $review->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $review->translateOrNew($locale)->fill($translation);
            }
            $review->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating service.'], 500);
        }

        return Response::json(ReviewResource::make($review));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $review = Review::find($id);
        if (!$review) {
            App::abort(404);
        }

        return Response::json(ReviewResource::make($review));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $review = Review::find($id);
        if (!$review) {
            App::abort(404);
        }

        $review->delete();
        return Response::json();
    }
}
