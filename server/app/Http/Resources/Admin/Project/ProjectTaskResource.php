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
			'code' => $this->code,
			'project_id' => $this->project_id,
			'category_id' => $this->category_id,
			'category' => ProjectTaskCategoryResource::make($this->whenLoaded('category')),
			'board_id' => $this->board_id,
			'board' => ProjectTaskBoardResource::make($this->whenLoaded('board')),
			'milestone_id' => $this->milestone_id,
			'user_id' => $this->user_id,
			'user_name' => $this->user?->name,
			'name' => $this->name,
			'description' => $this->description,
			'priority' => $this->priority,
			'estimated_hours' => $this->estimated_hours,
			'total_tracked_hours' => $this->total_tracked_hours,
			'due_date' => $this->due_date?->format('Y-m-d'),
			'completed_at' => $this->completed_at?->toIso8601String(),
			'position' => $this->position,
			'assignees' => $this->whenLoaded('assignees', function () {
				return $this->assignees->map(fn($u) => [
					'id' => $u->id,
					'name' => $u->name ?? $u->email,
				]);
			}),
			'comments_count' => $this->whenCounted('comments'),
			'comments' => ProjectTaskCommentResource::collection($this->whenLoaded('comments')),
			'time_entries' => ProjectTimeEntryResource::collection($this->whenLoaded('timeEntries')),
		];
	}
}
