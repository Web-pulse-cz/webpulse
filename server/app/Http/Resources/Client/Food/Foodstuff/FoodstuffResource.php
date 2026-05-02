<?php

namespace App\Http\Resources\Client\Food\Foodstuff;

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
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'macronutrients' => $this->macronutrients,
            'allergens' => $this->whenLoaded('allergens', fn () => $this->allergens->map(fn ($a) => [
                'id' => $a->id,
                'number' => $a->number,
                'name' => $a->name,
            ])),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])),
        ];
    }
}
