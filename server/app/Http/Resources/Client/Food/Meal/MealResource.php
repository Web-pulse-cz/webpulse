<?php

namespace App\Http\Resources\Client\Food\Meal;

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
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'price' => $this->price,
            'weight' => $this->weight,
            'recipe_id' => $this->recipe_id,
            'recipe' => $this->whenLoaded('recipe', function () {
                $r = $this->recipe;
                if (! $r) {
                    return null;
                }

                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'difficulty' => $r->difficulty,
                    'time_to_prepare' => $r->time_to_prepare,
                    'perex' => $r->perex,
                    'text' => $r->text,
                    'foodstuffs' => $r->foodstuffs->map(fn ($f) => [
                        'id' => $f->id,
                        'name' => $f->name,
                        'quantity' => $f->pivot->quantity,
                        'unit' => $f->pivot->unit,
                    ]),
                    'allergens' => $r->allergens->map(fn ($a) => [
                        'id' => $a->id,
                        'number' => $a->number,
                        'name' => $a->name,
                    ]),
                ];
            }),
            'allergens' => $this->whenLoaded('allergens', fn () => $this->allergens->map(fn ($a) => [
                'id' => $a->id,
                'number' => $a->number,
                'name' => $a->name,
            ])),
            'foodstuffs' => $this->whenLoaded('foodstuffs', fn () => $this->foodstuffs->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
            ])),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])),
        ];
    }
}
