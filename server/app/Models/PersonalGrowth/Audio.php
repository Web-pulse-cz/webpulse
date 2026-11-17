<?php

namespace App\Models\PersonalGrowth;

use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    protected $table = 'audios';

    protected $fillable = [
        'name',
        'speaker',
        'notes',
        'keywords',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];
}
