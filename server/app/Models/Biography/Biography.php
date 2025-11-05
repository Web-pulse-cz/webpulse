<?php

namespace App\Models\Biography;

use Illuminate\Database\Eloquent\Model;

class Biography extends Model
{
    protected $table = 'biographies';

    protected $fillable = [
        'name',
        'template',
        'phone_prefix',
        'phone',
        'email',
        'linkedin',
        'github',
        'website',
        'address',
        'about_me',
        'job_experiences',
        'education',
        'skills',
        'hard_skills',
        'soft_skills',
        'filename',
        'user_id',
        'job_title',
        'summary',
    ];

    protected $casts = [
        'job_experiences' => 'json',
        'education' => 'json',
        'skills' => 'json',
        'hard_skills' => 'json',
        'soft_skills' => 'json',
    ];
}
