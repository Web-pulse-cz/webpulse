<?php

namespace App\Http\Controllers\Admin\Service;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Service\ServiceResource;
use App\Models\Service\Service;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService();
    }
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Service::query()
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

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $service = Service::find($id);
            if (!$service) {
                App::abort(404);
            }
        } else {
            $service = new Service();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array'
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $service->fill($request->all());

            foreach ($request->translations as $locale => $translation) {
                $translation['slug'] = Str::slug($translation['name']);
                if ($locale != 'cs') {
                    foreach ($translation as $key => $value) {
                        if (in_array($value, ['', null])) {
                            $value = $request->translations['cs'][$key];
                        }
                        $value = $this->googleTranslatorService->translate($value, $locale);
                        $translation[$key] = $value;
                    }
                }
                $service->translateOrNew($locale)->fill($translation);
            }

            $service->save();
            $service->saveSites($service, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while updating service.'], 500);
        }

        return Response::json(ServiceResource::make($service));
    }

    public function show(Request $request, int $id): JsonResponse
    {
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

    public function destroy(int $id)
    {
        if (!$id) {
            App::abort(400);
        }

        $service = Service::find($id);
        if (!$service) {
            App::abort(404);
        }

        $service->delete();
        return Response::json();
    }
}
