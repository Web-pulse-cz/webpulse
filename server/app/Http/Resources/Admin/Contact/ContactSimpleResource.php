<?php

namespace App\Http\Resources\Admin\Contact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactSimpleResource extends JsonResource
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
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'phone_prefix' => $this->phone_prefix,
            'phone' => $this->phone,
            'email' => $this->email,
            'phase' => $this->phase?->name,
            'phase_color' => $this->phase?->color,
            'source' => $this->source?->name,
            'source_color' => $this->source?->color,
            'contact' => ContactSimpleResource::make($this->contact),
            'occupation' => $this->occupation,
            'goal' => $this->goal,
            'interests' => $this->interests,
            'note' => $this->note,
            'last_contacted_at' => $this->last_contacted_at,
            'formatted_last_contacted_at' => $this->last_contacted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
