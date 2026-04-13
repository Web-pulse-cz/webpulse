<?php

namespace App\Http\Resources\Admin\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActivityResource extends JsonResource
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
            'activity' => ActivityResource::make($this->activity),
            'activity_id' => (int) $this->activity_id,
            'description' => $this->description,
            'duration' => $this->duration,
            'completed' => $this->completed,
            'date' => $this->date,
            'formatted_date' => $this->date?->format('Y-m-d'),
            'formatted_day' => $this->date->format('d'),
        ];
    }
}
