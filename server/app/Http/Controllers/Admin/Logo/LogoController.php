<?php

namespace App\Http\Controllers\Admin\Logo;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Logo\LogoResource;
use App\Models\Logo\Logo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class LogoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Logo::query();

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('rating', 'like', '%' . $searchString . '%')
                    ->orWhere('position', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('name', 'like', '%' . $searchString . '%')
                    ->orWhereTranslation('url', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $logos = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => LogoResource::collection($logos->items()),
                'total' => $logos->total(),
                'perPage' => $logos->perPage(),
                'currentPage' => $logos->currentPage(),
                'lastPage' => $logos->lastPage(),
            ]);
        }

        $logos = $query->get();
        return Response::json(LogoResource::collection($logos));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $logo = Logo::find($id);
            if (!$logo) {
                App::abort(404);
            }
        } else {
            $logo = new Logo();
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|string',
            'translations' => 'required|array',
            'translations.*.url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $logo->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $logo->translateOrNew($locale)->fill($translation);
            }
            $logo->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating review.'], 500);
        }

        return Response::json(LogoResource::make($logo));
    }

    public function show(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $logo = Logo::find($id);
        if (!$logo) {
            App::abort(404);
        }

        return Response::json(LogoResource::make($logo));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $logo = Logo::find($id);
        if (!$logo) {
            App::abort(404);
        }

        $logo->delete();
        return Response::json();
    }
}
