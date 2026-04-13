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
            'price' => $this->price,
            'weight' => $this->weight,
            'recipe_id' => $this->recipe_id,
            'recipe' => $this->whenLoaded('recipe', function () {
                $r = $this->recipe;

                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'difficulty' => $r->difficulty,
                    'time_to_prepare' => $r->time_to_prepare,
                    'text' => $r->text,
                    'perex' => $r->perex,
                    'foodstuffs' => $r->foodstuffs->map(fn ($f) => [
                        'id' => $f->id,
                        'name' => $f->name,
                        'quantity' => $f->pivot->quantity,
                        'unit' => $f->pivot->unit,
                    ]),
                    'allergens' => $r->allergens->map(fn ($a) => [
                        'id' => $a->id,
                        'name' => $a->name,
                        'number' => $a->number,
                    ]),
                ];
            }),
            'allergens' => $this->allergens,
            'foodstuffs' => $this->foodstuffs,
            'categories' => $this->categories,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
        ];
    }
}
