<?php

namespace App\Http\Resources\Admin\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTimeEntryResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'task_id' => $this->task_id,
			'task_name' => $this->task?->name,
			'user_id' => $this->user_id,
			'user_name' => $this->user?->name,
			'description' => $this->description,
			'hours' => $this->hours,
			'hourly_rate' => $this->hourly_rate,
			'date' => $this->date?->format('Y-m-d'),
			'timer_started_at' => $this->timer_started_at?->toIso8601String(),
			'is_running' => $this->is_running,
		];
	}
}
