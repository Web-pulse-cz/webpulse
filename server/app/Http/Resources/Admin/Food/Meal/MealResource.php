<?php

namespace App\Http\Resources\Admin\Food\Meal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'allergens' => $this->allergens,
            'foodstuffs' => $this->foodstuffs,
            'categories' => $this->categories,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
        ];
    }
}