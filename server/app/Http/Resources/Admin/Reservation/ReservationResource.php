<?php

namespace App\Http\Resources\Admin\Reservation;

use App\Http\Resources\Admin\Apartment\ApartmentResource;
use App\Http\Resources\Admin\Currency\CurrencyResource;
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
            'nights' => $this->start_date && $this->end_date ? $this->start_date->diffInDays($this->end_date) : 0,
            'status' => $this->status,
            'source' => $this->source,
            'guest_firstname' => $this->guest_firstname,
            'guest_lastname' => $this->guest_lastname,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'number_of_guests' => $this->number_of_guests,
            'total_price' => $this->total_price,
            'currency_id' => $this->currency_id,
            'notes' => $this->notes,
            'apartment' => ApartmentResource::make($this->whenLoaded('apartment')),
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
