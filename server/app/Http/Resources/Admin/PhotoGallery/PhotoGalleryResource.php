<?php

namespace App\Http\Resources\Admin\PhotoGallery;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoGalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'active' => $this->active,
            'position' => $this->position,
            'name' => $this->name,
            'image' => $this->main_image,
            'images' => $this->images->pluck('filename')->values()->toArray(),
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
