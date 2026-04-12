<?php

namespace App\Http\Resources\Admin\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectNoteResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'user_id' => $this->user_id,
			'user_name' => $this->user?->name,
			'content' => $this->content,
			'created_at' => $this->created_at?->toIso8601String(),
		];
	}
}
