<?php

namespace App\Http\Resources\Admin\Event;

use App\Http\Resources\Admin\Country\CountryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRegistrationResource extends JsonResource
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
            'event_id' => $this->event_id,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'phone' => $this->phone,
            'note' => $this->note,
            'ico' => $this->ico,
            'dic' => $this->dic,
            'company' => $this->company,
            'street' => $this->street,
            'city' => $this->city,
            'zip' => $this->zip,
            'country_id' => $this->country_id,
            'country' => CountryResource::make($this->country),
            'is_paid' => $this->is_paid,
            'event' => [
                'id' => $this->event->id,
                'code' => $this->event->code,
                'name' => $this->event->name,
            ]
        ];
    }
}
