<?php

namespace App\Models\PersonalGrowth;

use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    use HasPersonalGrowthTasks;

    public const TYPES = [
        'seminar' => 'Seminář',
        'training' => 'Školení',
        'leadership' => 'Leadership',
    ];

    protected $table = 'seminars';

    protected $fillable = [
        'place',
        'date',
        'organizer',
        'type',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];
}
