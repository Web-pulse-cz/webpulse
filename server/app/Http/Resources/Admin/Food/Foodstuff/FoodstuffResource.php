<?php

namespace App\Http\Resources\Admin\Food\Foodstuff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodstuffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'macronutrients' => $this->macronutrients,
            'allergens' => $this->allergens,
            'categories' => $this->categories,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
        ];
    }
}