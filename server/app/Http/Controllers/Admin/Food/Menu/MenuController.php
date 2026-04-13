<?php

namespace App\Http\Controllers\Admin\Food\Menu;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Food\Menu\MenuResource;
use App\Models\Food\Allergen\Allergen;
use App\Models\Food\Meal\Meal;
use App\Models\Food\Menu\Menu;
use App\Models\Food\Menu\MenuItem;
use App\Services\GoogleTranslatorService;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $this->googleTranslatorService = new GoogleTranslatorService;
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
                $query->where($searchString[0], 'like', '%'.$searchString[1].'%');
            } else {
                $query->orWhereTranslation('name', 'like', '%'.$searchString.'%');
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

    public function store(Request $request, ?int $id = null): JsonResponse
    {
        if ($id) {
            $menu = Menu::find($id);
            if (! $menu) {
                App::abort(404);
            }
        } else {
            $menu = new Menu;
        }

        $validator = Validator::make($request->all(), [
            'translations' => 'required|array',
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

            // Sync menu items
            if ($request->has('items')) {
                $menu->items()->delete();
                $position = 0;
                foreach ($request->get('items', []) as $itemData) {
                    $menuItem = new MenuItem;
                    $menuItem->menu_id = $menu->id;
                    $menuItem->section_id = $itemData['section_id'] ?? null;
                    $menuItem->meal_id = $itemData['meal_id'] ?? null;
                    $menuItem->name = $itemData['name'] ?? '';
                    $menuItem->description = $itemData['description'] ?? null;
                    $menuItem->price = $itemData['price'] ?? 0;
                    $menuItem->weight = $itemData['weight'] ?? null;
                    $menuItem->allergen_ids = $itemData['allergen_ids'] ?? [];
                    $menuItem->position = $itemData['position'] ?? $position++;
                    $menuItem->save();
                }
            }

            $menu->saveSites($menu, $request->get('sites', []));

            DB::commit();
        } catch (\Throwable|\Exception $e) {
            DB::rollBack();

            return Response::json(['message' => 'Chyba při ukládání jídelního lístku.'], 500);
        }

        return Response::json(MenuResource::make($menu->fresh(['items.section', 'items.meal'])));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $menu = Menu::with(['items.section', 'items.meal'])
            ->whereRelation('sites', 'site_id', $siteId)
            ->find($id);

        if (! $menu) {
            App::abort(404);
        }

        return Response::json(MenuResource::make($menu));
    }

    public function destroy(int $id): JsonResponse
    {
        $menu = Menu::find($id);
        if (! $menu) {
            App::abort(404);
        }

        $menu->items()->delete();
        $menu->delete();

        return Response::json();
    }

    public function pdf(Request $request, int $id)
    {
        $menu = Menu::with(['items.section', 'items.meal.allergens'])->find($id);
        if (! $menu) {
            App::abort(404);
        }

        // Group items by section
        $sections = [];
        foreach ($menu->items as $item) {
            $sectionName = $item->section?->name ?? 'Ostatní';
            $sectionId = $item->section_id ?? 0;
            if (! isset($sections[$sectionId])) {
                $sections[$sectionId] = [
                    'name' => $sectionName,
                    'items' => [],
                ];
            }

            // Resolve allergens — from meal if linked, otherwise from allergen_ids
            $allergenNames = [];
            if ($item->meal_id && $item->meal?->allergens) {
                $allergenNames = $item->meal->allergens->pluck('name')->toArray();
            } elseif (! empty($item->allergen_ids)) {
                $allergenNames = Allergen::whereIn('id', $item->allergen_ids)->get()->pluck('name')->toArray();
            }

            $sections[$sectionId]['items'][] = [
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'weight' => $item->weight,
                'allergens' => $allergenNames,
            ];
        }

        $pdf = Pdf::loadView('pdf.menu', [
            'menu' => $menu,
            'sections' => $sections,
        ]);

        return $pdf->download('jidelni-listek-'.($menu->slug ?? $menu->id).'.pdf');
    }
}
