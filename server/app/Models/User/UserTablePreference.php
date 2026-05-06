<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserTablePreference extends Model
{
    protected $table = 'user_table_preferences';

    protected $fillable = [
        'user_id',
        'site_id',
        'table_slug',
        'visible_columns',
        'per_page',
    ];

    protected $casts = [
        'visible_columns' => 'array',
        'per_page' => 'integer',
    ];
}
