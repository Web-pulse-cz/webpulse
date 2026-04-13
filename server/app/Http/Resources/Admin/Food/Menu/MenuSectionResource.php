<?php

namespace App\Http\Resources\Admin\Food\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'position' => $this->position,
            'sites' => $this->whenLoaded('sites', fn () => $this->sites->pluck('id')),
        ];
    }
}
