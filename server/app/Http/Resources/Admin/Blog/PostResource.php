<?php

namespace App\Http\Resources\Admin\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            'status' => $this->status,
            'active' => $this->status === 'published',
            'published_from' => $this->published_from ? $this->published_from->toIso8601String() : null,
            'published_to' => $this->published_to ? $this->published_to->toIso8601String() : null,
            'name' => $this->name,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'categories' => $this->categories->pluck('id')->toArray(),
            'sites' => $this->sites
        ];
    }
}
