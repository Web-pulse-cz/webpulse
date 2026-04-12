<?php

namespace App\Http\Resources\Admin\Food\Foodstuff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodstuffCategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'category' => self::make($this->foodstuffCategory),
//            'categories' => self::make($this->foodstuffCategories),
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
        ];
    }
}
