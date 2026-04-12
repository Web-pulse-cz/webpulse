<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskBoard extends Model
{
	protected $table = 'project_task_boards';

	protected $fillable = [
		'project_id',
		'name',
		'color',
		'is_completed',
		'position',
	];

	protected $casts = [
		'is_completed' => 'boolean',
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'project_id', 'id');
	}

	public function tasks()
	{
		return $this->hasMany(ProjectTask::class, 'board_id', 'id')->orderBy('position');
	}
}
