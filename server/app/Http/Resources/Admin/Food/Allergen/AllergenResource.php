<?php

namespace App\Http\Resources\Admin\Food\Allergen;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllergenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'description' => $this->description,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
        ];
    }
}
