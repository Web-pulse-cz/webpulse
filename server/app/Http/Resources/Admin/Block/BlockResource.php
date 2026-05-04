<?php

namespace App\Http\Resources\Admin\Block;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
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
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
