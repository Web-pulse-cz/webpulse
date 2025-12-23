<?php

namespace App\Http\Resources\Admin\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCategoryResource extends JsonResource
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
            'position' => $this->position,
            'active' => $this->active,
            'name' => $this->name,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'posts_count' => $this->posts->count(),
        ];
    }
}
