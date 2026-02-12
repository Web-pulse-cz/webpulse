<?php

namespace App\Http\Resources\Admin\Logo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogoResource extends JsonResource
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
            'image' => $this->main_image,
            'name' => $this->name,
            'position' => $this->position,
            'url' => $this->url,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites
        ];
    }
}
