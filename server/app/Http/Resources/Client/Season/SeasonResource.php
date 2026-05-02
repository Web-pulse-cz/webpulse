<?php

namespace App\Http\Resources\Client\Season;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeasonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_recurring' => $this->is_recurring,
            'start_month' => $this->start_month,
            'start_day' => $this->start_day,
            'end_month' => $this->end_month,
            'end_day' => $this->end_day,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'color' => $this->color,
            'position' => $this->position,
            'name' => $this->name,
        ];
    }
}
