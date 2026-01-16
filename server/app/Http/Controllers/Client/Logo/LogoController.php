<?php

namespace App\Http\Controllers\Client\Logo;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Logo\LogoResource;
use App\Models\Logo\Logo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LogoController extends Controller
{
    public function index(Request $request, string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Logo::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('position', 'asc');

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
}
