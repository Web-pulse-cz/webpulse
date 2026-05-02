<?php

namespace App\Http\Controllers\Admin\PhotoGallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PhotoGallery\PhotoGalleryResource;
use App\Models\PhotoGallery\PhotoGallery;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PhotoGalleryController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService;
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = PhotoGallery::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%')
                    ->orWhereTranslation($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->whereTranslation('name', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('slug', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('description', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('meta_title', 'like', '%'.$searchString.'%')
                    ->orWhereTranslation('meta_description', 'like', '%'.$searchString.'%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $galleries = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => PhotoGalleryResource::collection($galleries->items()),
                'total' => $galleries->total(),
                'perPage' => $galleries->perPage(),
                'currentPage' => $galleries->currentPage(),
                'lastPage' => $galleries->lastPage(),
            ]);
        }

        $galleries = $query->get();

        return Response::json(PhotoGalleryResource::collection($galleries));
    }

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $gallery = PhotoGallery::find($id);
            if (! $gallery) {
                App::abort(404);
            }
        } else {
            $gallery = new PhotoGallery;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $gallery->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $gallery->translateOrNew($locale)->fill($translation);
            }

            $gallery->save();
            $gallery->saveImages($gallery, $request->get('images', []));
            $gallery->saveSites($gallery, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'An error occurred while updating photo gallery.'], 500);
        }

        return Response::json(PhotoGalleryResource::make($gallery));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $gallery = PhotoGallery::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (! $gallery) {
            App::abort(404);
        }

        return Response::json(PhotoGalleryResource::make($gallery));
    }

    public function destroy(int $id)
    {
        if (! $id) {
            App::abort(400);
        }

        $gallery = PhotoGallery::find($id);
        if (! $gallery) {
            App::abort(404);
        }

        $gallery->delete();

        return Response::json();
    }
}
