<?php

namespace App\Http\Controllers\Admin\Faq;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Faq\FaqResource;
use App\Models\Faq\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Faq::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%')
                    ->orWhereTranslation($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->whereTranslation('question', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('answer', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $faqs = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => FaqResource::collection($faqs->items()),
                'total' => $faqs->total(),
                'perPage' => $faqs->perPage(),
                'currentPage' => $faqs->currentPage(),
                'lastPage' => $faqs->lastPage(),
            ]);
        }

        $faqs = $query->get();
        return Response::json(FaqResource::collection($faqs));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $faq = Faq::find($id);
            if (!$faq) {
                App::abort(404);
            }
        } else {
            $faq = new Faq();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'translations.*.question' => 'required|string',
            'translations.*.answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $faq->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $faq->translateOrNew($locale)->fill($translation);
            }
            $faq->save();
            $faq->categories()->sync($request->get('categories', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => $e->getMessage()], 500);
            return Response::json(['message' => 'An error occurred while updating faq.'], 500);
        }

        return Response::json(FaqResource::make($faq));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $faq = Faq::find($id);
        if (!$faq) {
            App::abort(404);
        }

        return Response::json(FaqResource::make($faq));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $faq = Faq::find($id);
        if (!$faq) {
            App::abort(404);
        }

        $faq->delete();
        return Response::json();
    }
}
