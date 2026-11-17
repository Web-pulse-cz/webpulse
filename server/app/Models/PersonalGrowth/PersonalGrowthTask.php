<?php

namespace App\Models\PersonalGrowth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PersonalGrowthTask extends Model
{
    protected $table = 'personal_growth_tasks';

    protected $fillable = [
        'title',
        'is_done',
        'done_at',
        'position',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'done_at' => 'datetime',
        'position' => 'integer',
    ];

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }
}
