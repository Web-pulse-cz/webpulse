<?php

namespace App\Http\Resources\Admin\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventCategoryResource extends JsonResource
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
            'position' => $this->position,
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'events_count' => $this->events()->count(),
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
        ];
    }
}
