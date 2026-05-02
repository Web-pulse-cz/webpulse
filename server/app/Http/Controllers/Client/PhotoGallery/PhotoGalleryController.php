<?php

namespace App\Http\Controllers\Client\PhotoGallery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\PhotoGallery\PhotoGalleryResource;
use App\Models\PhotoGallery\PhotoGallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class PhotoGalleryController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = PhotoGallery::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('active', true)
            ->orderBy('position', 'asc');

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

        return Response::json(PhotoGalleryResource::collection($query->get()));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (! $id) {
            App::abort(400);
        }

        $gallery = PhotoGallery::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('active', true)
            ->find($id);

        if (! $gallery) {
            App::abort(404);
        }

        return Response::json(PhotoGalleryResource::make($gallery));
    }
}
