<?php

namespace App\Models\Project;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ProjectEvent extends Model
{
    protected $table = 'project_events';

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'user_id',
        'status_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class);
    }
}
