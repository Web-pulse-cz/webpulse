<?php

namespace App\Http\Resources\Client\Food\Allergen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllergenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
