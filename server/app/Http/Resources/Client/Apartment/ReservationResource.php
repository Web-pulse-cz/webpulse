<?php

namespace App\Http\Resources\Client\Apartment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'apartment_id' => $this->apartment_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'guest_firstname' => $this->guest_firstname,
            'guest_lastname' => $this->guest_lastname,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'number_of_guests' => $this->number_of_guests,
            'total_price' => $this->total_price,
            'currency_id' => $this->currency_id,
        ];
    }
}
