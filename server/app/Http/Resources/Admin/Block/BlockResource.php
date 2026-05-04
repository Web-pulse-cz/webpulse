<?php

namespace App\Http\Resources\Admin\Block;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $blockableTypes = array_flip(array_map(
            fn ($cls) => $cls,
            config('blocks.allowed_blockables', [])
        ));

        return [
            'id' => $this->id,
            'site_id' => $this->site_id,
            'blockable_type' => $this->blockable_type,
            'blockable_key' => $blockableTypes[$this->blockable_type] ?? null,
            'blockable_id' => $this->blockable_id,
            'type' => $this->type,
            'data' => $this->data ?? [],
            'position' => $this->position,
            'is_active' => $this->is_active,
            'translations' => array_column(
                $this->translations->map(fn ($t) => [
                    'locale' => $t->locale,
                    'data' => $t->data ?? [],
                ])->toArray(),
                null,
                'locale'
            ),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
