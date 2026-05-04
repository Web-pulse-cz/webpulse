<?php

namespace App\Http\Controllers\Client\Block;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\Block\BlockResource;
use App\Models\Block\Block;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

/**
 * Bloky obsahu (veřejné API).
 *
 * Frontend (Nuxt/Next/AI) si stáhne aktivní bloky pro konkrétní siteable entitu a
 * namapuje `type` na svou komponentu. Sdílená i překládatelná pole jsou sloučená do
 * jednoho `data` objektu — frontend nepotřebuje řešit, kde co je uloženo.
 */
class BlockController extends Controller
{
    /**
     * Seznam aktivních bloků pro daného rodiče.
     *
     * `blockableKey` je klíč z `config('blocks.allowed_blockables')` (např. `site`, `page`,
     * `apartment`). Volitelně lze předat `lang` v URL pro přepnutí lokalizace překladů
     * (jinak se použije aktuální locale aplikace, fallback na `app.fallback_locale`).
     * Vrací se pouze bloky s `is_active = true`, seřazené podle `position`.
     */
    public function index(string $blockableKey, int $blockableId, ?string $lang = null): JsonResponse
    {
        $this->handleLanguage($lang);

        $allowed = config('blocks.allowed_blockables', []);
        if (! isset($allowed[$blockableKey])) {
            App::abort(404);
        }

        $items = Block::query()
            ->with('translations')
            ->where('blockable_type', $allowed[$blockableKey])
            ->where('blockable_id', $blockableId)
            ->where('is_active', true)
            ->orderBy('position')
            ->get();

        return Response::json(BlockResource::collection($items));
    }
}
