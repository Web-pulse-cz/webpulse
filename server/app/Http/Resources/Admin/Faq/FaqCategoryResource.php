<?php

namespace App\Http\Resources\Admin\Faq;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqCategoryResource extends JsonResource
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
            'position' => $this->position,
            'active' => $this->active,
            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'faqs_count' => $this->faqs->count(),
        ];
    }
}
