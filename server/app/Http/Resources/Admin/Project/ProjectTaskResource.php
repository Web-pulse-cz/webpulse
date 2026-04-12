<?php

namespace App\Http\Resources\Admin\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'milestone_id' => $this->milestone_id,
			'user_id' => $this->user_id,
			'user_name' => $this->user?->name,
			'name' => $this->name,
			'description' => $this->description,
			'status' => $this->status,
			'priority' => $this->priority,
			'estimated_hours' => $this->estimated_hours,
			'due_date' => $this->due_date?->format('Y-m-d'),
			'completed_at' => $this->completed_at?->toIso8601String(),
			'position' => $this->position,
		];
	}
}
