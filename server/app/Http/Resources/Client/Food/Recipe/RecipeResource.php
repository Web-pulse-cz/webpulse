<?php

namespace App\Http\Resources\Client\Food\Recipe;

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
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'difficulty' => $this->difficulty,
            'time_to_prepare' => $this->time_to_prepare,
            'allergens' => $this->whenLoaded('allergens', fn () => $this->allergens->map(fn ($a) => [
                'id' => $a->id,
                'number' => $a->number,
                'name' => $a->name,
            ])),
            'foodstuffs' => $this->whenLoaded('foodstuffs', fn () => $this->foodstuffs->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'quantity' => $f->pivot->quantity,
                'unit' => $f->pivot->unit,
            ])),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])),
        ];
    }
}
