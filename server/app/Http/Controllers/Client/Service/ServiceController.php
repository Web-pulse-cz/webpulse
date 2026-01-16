<?php

namespace App\Http\Controllers\Client\Service;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Service\ServiceResource;
use App\Models\Service\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class ServiceController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Service::query();

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
        } else {
            $query->orderBy('id', 'desc');
        }

        if ($request->has('paginate')) {
            $services = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => ServiceResource::collection($services->items()),
                'total' => $services->total(),
                'perPage' => $services->perPage(),
                'currentPage' => $services->currentPage(),
                'lastPage' => $services->lastPage(),
            ]);
        }

        $services = $query->get();
        return Response::json(ServiceResource::collection($services));
    }

    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $service = Service::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (!$service) {
            App::abort(404);
        }

        return Response::json(ServiceResource::make($service));
    }
}
