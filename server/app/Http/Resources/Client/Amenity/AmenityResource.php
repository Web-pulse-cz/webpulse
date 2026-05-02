<?php

namespace App\Http\Resources\Client\Amenity;

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
        ];
    }
}
