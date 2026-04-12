<?php

namespace App\Models\Project;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
	protected $table = 'project_tasks';

	protected $fillable = [
		'project_id',
		'milestone_id',
		'user_id',
		'name',
		'description',
		'status',
		'priority',
		'estimated_hours',
		'due_date',
		'completed_at',
		'position',
	];

	protected $casts = [
		'due_date' => 'date',
		'completed_at' => 'datetime',
		'estimated_hours' => 'decimal:2',
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'project_id', 'id');
	}

	public function milestone()
	{
		return $this->belongsTo(ProjectMilestone::class, 'milestone_id', 'id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function timeEntries()
	{
		return $this->hasMany(ProjectTimeEntry::class, 'task_id', 'id');
	}
}
