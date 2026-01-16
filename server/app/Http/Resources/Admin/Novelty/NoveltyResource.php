<?php

namespace App\Http\Resources\Admin\Novelty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoveltyResource extends JsonResource
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
            'active' => $this->active,
            'priority' => $this->priority,
            'name' => $this->name,
            'slug' => $this->slug,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites
        ];
    }
}
