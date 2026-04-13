<?php

namespace App\Models\Project;

use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class ProjectStatus extends Model
{
	use Siteable;

	protected $table = 'project_statuses';

	protected $fillable = [
		'name',
		'color',
		'position',
		'is_closed',
	];

	protected $casts = [
		'is_closed' => 'boolean',
	];

	public function projects()
	{
		return $this->hasMany(Project::class, 'status_id', 'id');
	}

	public function sites()
	{
		return $this->morphToMany('App\Models\Site\Site', 'siteable');
	}
}
