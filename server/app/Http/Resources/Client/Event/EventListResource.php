<?php

namespace App\Http\Resources\Client\Event;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListResource extends JsonResource
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
            'image' => $this->image,
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'place' => $this->place,
            'is_online' => $this->is_online,
            'max_participants' => $this->max_participants,
            'registration_required' => $this->registration_required,
            'price' => $this->price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'category' => EventCategoryResource::make($this->category),
        ];
    }
}
