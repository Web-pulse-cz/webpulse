<?php

namespace App\Http\Resources\Client\Block;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale'));

        return [
            'id' => $this->id,
            'type' => $this->type,
            'position' => $this->position,
            'data' => array_merge(
                $this->data ?? [],
                $translation?->data ?? []
            ),
        ];
    }
}
