<?php

namespace App\Http\Resources\Admin\PersonalGrowth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'author' => $this->author,
            'notes' => $this->notes,
            'keywords' => $this->keywords ?? [],
            'is_read' => $this->is_read,
            'image' => $this->main_image,
            'tasks_total' => $this->whenCounted('tasks'),
            'tasks_done' => $this->whenCounted('doneTasks'),
            'tasks' => PersonalGrowthTaskResource::collection($this->whenLoaded('tasks')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
