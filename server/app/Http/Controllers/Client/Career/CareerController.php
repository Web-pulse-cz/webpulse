<?php

namespace App\Http\Controllers\Client\Career;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Career\CareerResource;
use App\Models\Career\Career;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Career::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('status', 'open');

        if($request->has('search') && in_array($request->input('search'), ['', null])) {
            $query->where('code', 'like', '%' . $request->input('search') . '%')
                ->orWhereTranslation('name', 'like', '%' . $request->input('search') . '%')
                ->orWhereTranslation('location', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->has('paginate')) {
            $careers = $query->paginate((int)$request->get('paginate'));

            return Response::json([
                'data' => CareerResource::collection($careers->items()),
                'total' => $careers->total(),
                'perPage' => $careers->perPage(),
                'currentPage' => $careers->currentPage(),
                'lastPage' => $careers->lastPage(),
            ]);
        }

        $careers = $query->get();
        return Response::json(CareerResource::collection($careers));

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, int $id, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if(!$id) {
            App::abort(400, 'Invalid career ID');
        }

        $career = Career::query
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if(!$career) {
            App::abort(404, 'Career not found');
        }

        return Response::json(CareerResource::make($career));
    }
}
