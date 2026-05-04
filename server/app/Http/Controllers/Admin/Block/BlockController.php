<?php

namespace App\Http\Controllers\Admin\Block;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Block\BlockResource;
use App\Models\Block\Block;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Bloky obsahu (admin).
 *
 * Bloky jsou polymorfní obsahové sekce (hero, about, gallery…) přiřazené k libovolné
 * `siteable` entitě (Site, Page, Apartment…). Definice typů a polí je v `config/blocks.php`,
 * frontend je čte přes `GET /api/admin/block/schemas` a renderuje formuláře dynamicky.
 *
 * Všechny endpointy vyžadují Sanctum auth, hlavičku `X-Site-Hash` a oprávnění modulu `blocks`.
 */
class BlockController extends Controller
{
    /**
     * Vrátí registr typů bloků a povolených rodičů.
     *
     * Tento endpoint slouží adminu k dynamickému vykreslení formulářů. Pro každý typ
     * vrací seznam polí včetně příznaku `translatable` — sdílená pole se ukládají do
     * `blocks.data`, překládatelná do `block_translations.data` per locale.
     */
    public function schemas(): JsonResponse
    {
        $allowed = config('blocks.allowed_blockables', []);
        $types = config('blocks.types', []);

        return Response::json([
            'allowed_blockables' => array_map(fn ($key) => [
                'key' => $key,
                'class' => $allowed[$key],
            ], array_keys($allowed)),
            'types' => array_map(fn ($key) => [
                'key' => $key,
                'label' => $types[$key]['label'] ?? $key,
                'description' => $types[$key]['description'] ?? null,
                'fields' => array_map(fn ($name) => array_merge(
                    ['name' => $name],
                    $types[$key]['fields'][$name]
                ), array_keys($types[$key]['fields'] ?? [])),
            ], array_keys($types)),
        ]);
    }

    /**
     * Seznam bloků pro aktuální site.
     *
     * Volitelné filtry: `type` (slug typu bloku), `blockable_type` (klíč rodiče dle
     * `allowed_blockables`) a `blockable_id`. Při zaslání `paginate` vrací stránkovaný
     * objekt `{data, total, perPage, currentPage, lastPage}`, jinak prosté pole.
     */
    public function index(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $query = Block::query()
            ->with('translations')
            ->where('site_id', $siteId);

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('blockable_type')) {
            $query->where('blockable_type', $this->resolveBlockableClass($request->get('blockable_type')));
        }

        if ($request->filled('blockable_id')) {
            $query->where('blockable_id', $request->get('blockable_id'));
        }

        if ($request->has('orderBy') && $request->has('orderWay')) {
            $query->orderBy($request->get('orderBy'), $request->get('orderWay'));
        } else {
            $query->orderBy('position');
        }

        if ($request->has('paginate')) {
            $items = $query->paginate($request->get('paginate'));

            return Response::json([
                'data' => BlockResource::collection($items->items()),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'lastPage' => $items->lastPage(),
            ]);
        }

        return Response::json(BlockResource::collection($query->get()));
    }

    /**
     * Detail jednoho bloku včetně všech překladů.
     *
     * Blok musí patřit aktuálnímu site (resolved z `X-Site-Hash`), jinak 404.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $item = Block::with('translations')
            ->where('site_id', $siteId)
            ->find($id);

        if (! $item) {
            App::abort(404);
        }

        return Response::json(BlockResource::make($item));
    }

    /**
     * Vytvoří nový blok nebo aktualizuje existující.
     *
     * `id` v URL je nepovinné — pokud chybí, vytvoří se nový záznam, jinak se aktualizuje.
     * `blockable_type` je klíč z `config('blocks.allowed_blockables')` (např. `site`, `page`,
     * `apartment`), nikoliv FQN třídy. Pole `data` obsahuje sdílené (ne-translatable) hodnoty,
     * `translations[locale].data` obsahuje překládatelné. Backend filtruje obě podle definice
     * v registru — neznámá pole se zahodí.
     */
    public function store(Request $request, ?int $id = null)
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        if ($id) {
            $item = Block::where('site_id', $siteId)->find($id);
            if (! $item) {
                App::abort(404);
            }
        } else {
            $item = new Block;
        }

        $allowedTypes = array_keys(config('blocks.types', []));
        $allowedBlockables = array_keys(config('blocks.allowed_blockables', []));

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:'.implode(',', $allowedTypes),
            'blockable_type' => 'required|string|in:'.implode(',', $allowedBlockables),
            'blockable_id' => 'required|integer|min:1',
            'position' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'data' => 'nullable|array',
            'translations' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        $blockableClass = $this->resolveBlockableClass($request->get('blockable_type'));

        if (! $blockableClass::query()->whereKey($request->get('blockable_id'))->exists()) {
            return Response::json(['blockable' => 'Vybraný rodič neexistuje.'], 400);
        }

        DB::beginTransaction();
        try {
            $item->fill([
                'site_id' => $siteId,
                'blockable_type' => $blockableClass,
                'blockable_id' => $request->get('blockable_id'),
                'type' => $request->get('type'),
                'data' => $this->filterDataByFields($request->get('type'), $request->get('data', []), false),
                'position' => $request->get('position', 0),
                'is_active' => $request->boolean('is_active', true),
            ]);
            $item->save();

            foreach ((array) $request->get('translations', []) as $locale => $translation) {
                $translationData = $this->filterDataByFields(
                    $request->get('type'),
                    $translation['data'] ?? $translation ?? [],
                    true
                );
                $item->translateOrNew($locale)->fill(['data' => $translationData]);
            }
            $item->save();

            DB::commit();

            return Response::json(BlockResource::make($item->fresh('translations')));
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while saving the block.'], 500);
        }
    }

    /**
     * Hromadně přeřadí pořadí bloků.
     *
     * Tělo: `{ "order": [{"id": 1, "position": 0}, {"id": 2, "position": 1}, ...] }`.
     * Update se provádí v transakci a aplikuje se pouze na bloky aktuálního site.
     */
    public function reorder(Request $request): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $validator = Validator::make($request->all(), [
            'order' => 'required|array',
            'order.*.id' => 'required|integer',
            'order.*.position' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Response::json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            foreach ($request->get('order') as $entry) {
                Block::where('site_id', $siteId)
                    ->where('id', $entry['id'])
                    ->update(['position' => $entry['position']]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return Response::json(['error' => 'An error occurred while reordering blocks.'], 500);
        }

        return Response::json();
    }

    /**
     * Smaže blok i jeho překlady.
     *
     * Cascade na `block_translations` zajišťuje DB. 404 pokud blok nepatří aktuálnímu site.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $item = Block::where('site_id', $siteId)->find($id);
        if (! $item) {
            App::abort(404);
        }

        $item->delete();

        return Response::json();
    }

    protected function resolveBlockableClass(string $key): string
    {
        $allowed = config('blocks.allowed_blockables', []);

        if (isset($allowed[$key])) {
            return $allowed[$key];
        }

        if (in_array($key, $allowed, true)) {
            return $key;
        }

        App::abort(400, 'Invalid blockable type.');
    }

    protected function filterDataByFields(string $type, array $data, bool $translatable): array
    {
        $fields = config("blocks.types.$type.fields", []);
        $filtered = [];

        foreach ($fields as $name => $def) {
            if ((bool) ($def['translatable'] ?? false) !== $translatable) {
                continue;
            }
            if (array_key_exists($name, $data)) {
                $filtered[$name] = $data[$name];
            }
        }

        return $filtered;
    }
}
