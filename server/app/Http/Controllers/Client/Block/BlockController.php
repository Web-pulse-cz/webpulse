<?php

namespace App\Http\Controllers\Client\Block;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Block\BlockResource;
use App\Models\Block\Block;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * Bloky obsahu (veřejné API).
 *
 * Frontend (Nuxt/Next/AI) si stáhne aktivní bloky pro konkrétní web (resolved z
 * `X-Site-Hash`) a namapuje `type` na svou komponentu. Sdílená i překládatelná pole
 * jsou sloučená do jednoho `data` objektu.
 */
class BlockController extends Controller
{
    /**
     * Seznam aktivních bloků pro aktuální web.
     *
     * Volitelně lze předat `lang` v URL pro přepnutí lokalizace překladů (jinak se
     * použije aktuální locale aplikace, fallback na `app.fallback_locale`). Vrací se
     * pouze bloky s `is_active = true`, seřazené podle `position`.
     */
    public function index(Request $request, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);
        $siteId = $this->handleSite($request->header('X-Site-Hash'));

        $items = Block::query()
            ->with('translations')
            ->whereRelation('sites', 'site_id', $siteId)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return Response::json(BlockResource::collection($items));
    }
}
