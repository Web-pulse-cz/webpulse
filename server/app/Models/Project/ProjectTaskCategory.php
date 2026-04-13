<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskCategory extends Model
{
    protected $table = 'project_task_categories';

    protected $fillable = [
        'project_id',
        'name',
        'color',
        'position',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'category_id', 'id');
    }
}
