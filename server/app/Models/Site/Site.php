<?php

namespace App\Models\Site;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $table = 'sites';

    protected $fillable = [
        'name',
        'url',
        'hash',
        'is_secure',
        'is_active',
        'settings',
        'user_id',
    ];

    protected $casts = [
        'is_secure' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'json'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'sites_has_users', 'site_id');
    }
}
