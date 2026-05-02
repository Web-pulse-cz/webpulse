<?php

namespace App\Http\Resources\Client\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantTableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'seats' => $this->seats,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status,
            'position' => $this->position,
        ];
    }
}
