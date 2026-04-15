<?php

namespace App\Http\Resources\Admin\Task;

use App\Http\Resources\Admin\Project\ProjectTaskResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskBoardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'is_completed' => $this->is_completed,
            'position' => $this->position,
            'tasks_count' => $this->whenCounted('tasks'),
            'tasks' => ProjectTaskResource::collection($this->whenLoaded('tasks')),
            'sites' => $this->whenLoaded('sites', fn () => $this->sites->pluck('id')),
        ];
    }
}
