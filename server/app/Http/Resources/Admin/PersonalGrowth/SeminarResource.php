<?php

namespace App\Http\Resources\Admin\PersonalGrowth;

use App\Models\PersonalGrowth\Seminar;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeminarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'place' => $this->place,
            'date' => $this->date?->format('Y-m-d'),
            'organizer' => $this->organizer,
            'type' => $this->type,
            'type_label' => Seminar::TYPES[$this->type] ?? $this->type,
            'notes' => $this->notes,
            'tasks_total' => $this->whenCounted('tasks'),
            'tasks_done' => $this->whenCounted('doneTasks'),
            'tasks' => PersonalGrowthTaskResource::collection($this->whenLoaded('tasks')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
