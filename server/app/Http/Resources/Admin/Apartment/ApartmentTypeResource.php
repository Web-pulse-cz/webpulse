<?php

namespace App\Http\Resources\Admin\Apartment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => $this->main_image,
            'images' => $this->images,
            'position' => $this->position,
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'apartments_count' => $this->whenLoaded('apartments', fn () => $this->apartments->count()),
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
