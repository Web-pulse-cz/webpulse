<?php

namespace App\Http\Resources\Admin\Food\Recipe;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'difficulty' => $this->difficulty,
            'time_to_prepare' => $this->time_to_prepare,
            'allergens' => $this->allergens,
            'foodstuffs' => $this->foodstuffs->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'quantity' => $f->pivot->quantity,
                'unit' => $f->pivot->unit,
            ]),
            'categories' => $this->categories,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
        ];
    }
}