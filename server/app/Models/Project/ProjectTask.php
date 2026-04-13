<?php

namespace App\Models\Project;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectTask extends Model
{
    protected $table = 'project_tasks';

    protected $fillable = [
        'project_id',
        'milestone_id',
        'category_id',
        'board_id',
        'user_id',
        'name',
        'description',
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

    protected static function booted(): void
    {
        static::creating(function (ProjectTask $task) {
            if (! $task->code) {
                $task->code = static::generateNextCode($task->project_id);
            }
        });
    }

    public static function generateNextCode(?int $projectId): string
    {
        $prefix = 'UO';
        if ($projectId) {
            $project = Project::find($projectId);
            $prefix = $project?->prefix ?? 'UO';
        }

        $sequence = DB::table('project_task_code_sequences')
            ->where('project_id', $projectId)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            DB::table('project_task_code_sequences')->insert([
                'project_id' => $projectId,
                'last_number' => 1,
            ]);
            $nextNumber = 1;
        } else {
            $nextNumber = $sequence->last_number + 1;
            DB::table('project_task_code_sequences')
                ->where('project_id', $projectId)
                ->update(['last_number' => $nextNumber]);
        }

        return sprintf('%s-%08d', strtoupper($prefix), $nextNumber);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function milestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(ProjectTaskCategory::class, 'category_id', 'id');
    }

    public function board()
    {
        return $this->belongsTo(ProjectTaskBoard::class, 'board_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'project_task_assignees', 'task_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(ProjectTaskComment::class, 'task_id', 'id')->orderBy('created_at', 'desc');
    }

    public function timeEntries()
    {
        return $this->hasMany(ProjectTimeEntry::class, 'task_id', 'id');
    }

    public function getTotalTrackedHoursAttribute(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }
}
