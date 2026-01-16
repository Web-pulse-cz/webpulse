<?php

namespace App\Http\Controllers\Admin\Novelty;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Novelty\NoveltyResource;
use App\Models\Novelty\Novelty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NoveltyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Novelty::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->where('type', 'like', '%' . $searchString . '%')
                    ->orWhere('price_type', 'like', '%' . $searchString . '%')
                    ->orWhere('price', '=', $searchString)
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
            $noveltys = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => NoveltyResource::collection($noveltys->items()),
                'total' => $noveltys->total(),
                'perPage' => $noveltys->perPage(),
                'currentPage' => $noveltys->currentPage(),
                'lastPage' => $noveltys->lastPage(),
            ]);
        }

        $noveltys = $query->get();
        return Response::json(NoveltyResource::collection($noveltys));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $novelty = Novelty::find($id);
            if (!$novelty) {
                App::abort(404);
            }
        } else {
            $novelty = new Novelty();
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
            $novelty->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                $novelty->translateOrNew($locale)->fill($translation);
            }

            $novelty->saveImages($novelty, $request->get('image'));
            $novelty->saveSites($novelty, $request->get('sites', []));

            $novelty->save();

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating novelty.'], 500);
        }

        return Response::json(NoveltyResource::make($novelty));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $novelty = Novelty::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (!$novelty) {
            App::abort(404);
        }

        return Response::json(NoveltyResource::make($novelty));
    }

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $novelty = Novelty::find($id);
        if (!$novelty) {
            App::abort(404);
        }

        $novelty->delete();
        return Response::json();
    }
}
