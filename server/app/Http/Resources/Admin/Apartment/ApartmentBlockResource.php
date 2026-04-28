<?php

namespace App\Http\Resources\Admin\Apartment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'apartment_id' => $this->apartment_id,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'reason' => $this->reason,
            'note' => $this->note,
            'apartment' => ApartmentResource::make($this->whenLoaded('apartment')),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
