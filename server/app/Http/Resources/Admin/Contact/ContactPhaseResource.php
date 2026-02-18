<?php

namespace App\Http\Resources\Admin\Contact;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactPhaseResource extends JsonResource
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
            'color' => $this->color,
            'contacts_count' => $this->contacts->count(),
            'position' => $this->position,
            'show_in_statistics' => $this->show_in_statistics,
            //'tasks' => ContactTaskResource::make($this->tasks),
        ];
    }
}
