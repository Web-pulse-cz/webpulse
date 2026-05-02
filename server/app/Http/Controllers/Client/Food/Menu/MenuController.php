<?php

namespace App\Http\Controllers\Client\Food\Menu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Food\Menu\MenuResource;
use App\Models\Food\Menu\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class MenuController extends Controller
{
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $menus = Menu::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->orderBy('id', 'desc')
            ->get();

        return Response::json(MenuResource::collection($menus));
    }

    public function show(Request $request, int $id, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $menu = Menu::query()
            ->with(['items.section', 'items.meal'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $menu) {
            App::abort(404);
        }

        return Response::json(MenuResource::make($menu));
    }
}
