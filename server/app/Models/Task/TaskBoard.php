<?php

namespace App\Models\Task;

use App\Models\Project\ProjectTask;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class TaskBoard extends Model
{
    use Siteable;

    protected $table = 'task_boards';

    protected $fillable = [
        'name',
        'color',
        'is_completed',
        'position',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'global_board_id', 'id')->orderBy('position');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
