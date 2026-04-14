<?php

namespace App\Models\Project;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    protected $table = 'project_milestones';

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'due_date',
        'completed_at',
        'position',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'milestone_id', 'id')->orderBy('position');
    }
}
