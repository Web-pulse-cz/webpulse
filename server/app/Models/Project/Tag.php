<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
	protected $table = 'tags';

	protected $fillable = [
		'name',
		'color',
	];

	public function projects()
	{
		return $this->belongsToMany(Project::class, 'project_tag', 'tag_id', 'project_id');
	}
}
