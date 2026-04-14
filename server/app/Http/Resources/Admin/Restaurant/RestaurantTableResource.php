<?php

namespace App\Http\Resources\Admin\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantTableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'seats' => $this->seats,
            'location' => $this->location,
            'description' => $this->description,
            'status' => $this->status,
            'position' => $this->position,
            'upcoming_reservations' => ReservationResource::collection($this->whenLoaded('upcomingReservations')),
            'today_reservations' => ReservationResource::collection($this->whenLoaded('todayReservations')),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
            'upcoming_count' => $this->whenCounted('upcomingReservations'),
            'sites' => $this->whenLoaded('sites', fn () => $this->sites->pluck('id')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
