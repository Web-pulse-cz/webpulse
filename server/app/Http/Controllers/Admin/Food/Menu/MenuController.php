<?php

namespace App\Http\Controllers\Admin\Food\Menu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Menu\MenuResource;
use App\Models\Food\Menu\Menu;
use App\Services\GoogleTranslatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    protected GoogleTranslatorService $googleTranslatorService;

    public function __construct()
    {
        $this->googleTranslatorService = new GoogleTranslatorService();
    }

    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));
        $query = Menu::query()
            ->whereRelation('sites', 'site_id', $siteId);

        if ($request->has('search') && $request->get('search') != '' && $request->get('search') != null) {
            $searchString = $request->get('search');
            if (str_contains(':', $searchString)) {
                $searchString = explode(':', $searchString);
                $query->where($searchString[0], 'like', '%' . $searchString[1] . '%');
            } else {
                $query->orWhereTranslation('name', 'like', '%' . $searchString . '%');
            }
        }

        if ($request->has('orderWay') && $request->get('orderBy')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        }

        if ($request->has('paginate')) {
            $menus = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => MenuResource::collection($menus->items()),
                'total' => $menus->total(),
                'perPage' => $menus->perPage(),
                'currentPage' => $menus->currentPage(),
                'lastPage' => $menus->lastPage(),
            ]);
        }

        $menus = $query->get();
        return Response::json(MenuResource::collection($menus));
    }

    public function store(Request $request, int $id = null): JsonResponse
    {
        if ($id) {
            $menu = Menu::find($id);
            if (!$menu) {
                App::abort(404);
            }
        } else {
            $menu = new Menu();
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
            'meals' => 'nullable|array',
            'meals.*' => 'integer|exists:meals,id',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->translations as $locale => $translation) {
                $translation = $this->googleTranslatorService->parseTranslation($request, $translation, $locale);
                $menu->translateOrNew($locale)->fill($translation);
            }

            $menu->save();

            $menu->meals()->sync($request->get('meals', []));

            $menu->saveSites($menu, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();
            return Response::json(['message' => 'An error occurred while saving menu.'], 500);
        }

        return Response::json(MenuResource::make($menu));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if (!$id) {
            App::abort(400);
        }

        $menu = Menu::query()
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);
        if (!$menu) {
            App::abort(404);
        }

        return Response::json(MenuResource::make($menu));
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$id) {
            App::abort(400);
        }

        $menu = Menu::find($id);
        if (!$menu) {
            App::abort(404);
        }

        $menu->delete();
        return Response::json();
    }
}
