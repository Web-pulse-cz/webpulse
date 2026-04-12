<?php

namespace App\Models\Project;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ProjectNote extends Model
{
	protected $table = 'project_notes';

	protected $fillable = [
		'project_id',
		'user_id',
		'content',
	];

	public function project()
	{
		return $this->belongsTo(Project::class, 'project_id', 'id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
