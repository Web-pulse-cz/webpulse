<?php

namespace App\Http\Resources\Admin\Shift;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shift_template_id' => $this->shift_template_id,
            'template' => ShiftTemplateResource::make($this->whenLoaded('template')),
            'date' => $this->date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'break_minutes' => $this->break_minutes,
            'duration_hours' => $this->duration_hours,
            'location' => $this->location,
            'note' => $this->note,
            'employees' => $this->whenLoaded('employees', function () {
                return $this->employees->map(fn ($e) => [
                    'id' => $e->id,
                    'full_name' => $e->full_name,
                    'photo' => $e->photo,
                    'pivot_status' => $e->pivot->status,
                    'pivot_note' => $e->pivot->note,
                ]);
            }),
        ];
    }
}
