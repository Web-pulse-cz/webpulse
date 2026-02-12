<?php

namespace App\Models\Changelog;

use Illuminate\Database\Eloquent\Model;

class Changelog extends Model
{
    protected $table = 'changelogs';

    protected $fillable = [
        'version',
        'title',
        'subtitle',
        'description',
        'type',
        'priority'
    ];
}
