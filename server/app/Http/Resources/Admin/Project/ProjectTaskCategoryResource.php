<?php

namespace App\Http\Resources\Admin\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskCategoryResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'project_id' => $this->project_id,
			'name' => $this->name,
			'color' => $this->color,
			'position' => $this->position,
			'tasks_count' => $this->whenCounted('tasks'),
		];
	}
}
