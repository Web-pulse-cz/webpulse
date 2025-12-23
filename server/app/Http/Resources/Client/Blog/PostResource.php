<?php

namespace App\Http\Resources\Client\Blog;

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
            'published_from' => $this->published_from,
            'published_to' => $this->published_to,
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'categories' => PostCategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at,
        ];
    }
}
