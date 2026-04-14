<?php

namespace App\Http\Resources\Admin\Food\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
            'sites' => $this->sites,
        ];
    }
}
