<?php

namespace App\Models\Project;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskComment extends Model
{
    protected $table = 'project_task_comments';

    protected $fillable = [
        'task_id',
        'user_id',
        'content',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
