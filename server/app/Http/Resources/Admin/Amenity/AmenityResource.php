<?php

namespace App\Http\Resources\Admin\Amenity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AmenityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'icon' => $this->icon,
            'position' => $this->position,
            'name' => $this->name,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
