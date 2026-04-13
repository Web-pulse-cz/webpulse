<?php

namespace App\Http\Controllers\Admin\Language;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Language\LanguageResource;
use App\Models\Language\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class LanguageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Language::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->where('code', '=', $searchString)
                    ->orWhere('iso', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('name', 'like', '%'.$searchString.'%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $languages = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => LanguageResource::collection($languages->items()),
                'total' => $languages->total(),
                'perPage' => $languages->perPage(),
                'currentPage' => $languages->currentPage(),
                'lastPage' => $languages->lastPage(),
            ]);
        }

        $languages = $query->get();

        return Response::json(LanguageResource::collection($languages));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $language = Language::find($id);
            if (! $language) {
                App::abort(404);
            }
        } else {
            $language = new Language;
        }

        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'translations' => 'required|array',
            'translations.*.name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $language->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $language->translateOrNew($locale)->fill($translation);
            }
            $language->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while updating language.'], 500);
        }

        return Response::json(LanguageResource::make($language));
    }

    public function show(int $id): JsonResponse
    {
        if (! $id) {
            App::abort(400);
        }

        $language = Language::find($id);
        if (! $language) {
            App::abort(404);
        }

        return Response::json(LanguageResource::make($language));
    }

    public function destroy(int $id)
    {
        if (! $id) {
            App::abort(400);
        }

        $language = Language::find($id);
        if (! $language) {
            App::abort(404);
        }

        $language->delete();

        return Response::json();
    }
}
