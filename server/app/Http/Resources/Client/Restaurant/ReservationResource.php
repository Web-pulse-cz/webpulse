<?php

namespace App\Http\Resources\Client\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_id' => $this->table_id,
            'date' => $this->date?->format('Y-m-d'),
            'time_from' => $this->time_from,
            'time_to' => $this->time_to,
            'guest_first_name' => $this->guest_first_name,
            'guest_last_name' => $this->guest_last_name,
            'guests_count' => $this->guests_count,
            'status' => $this->status,
        ];
    }
}
