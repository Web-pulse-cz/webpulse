<?php

namespace App\Models\Project;

use App\Models\Site\Site;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ProjectTimeEntry extends Model
{
    protected $table = 'project_time_entries';

    protected $fillable = [
        'site_id',
        'project_id',
        'task_id',
        'user_id',
        'description',
        'hours',
        'hourly_rate',
        'date',
        'timer_started_at',
        'is_running',
    ];

    protected $casts = [
        'date' => 'date',
        'timer_started_at' => 'datetime',
        'is_running' => 'boolean',
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }
}
