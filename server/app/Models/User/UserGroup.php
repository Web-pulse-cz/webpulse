<?php

namespace App\Models\User;

use App\Models\Site\Site;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory, Siteable;

    public $table = 'user_groups';

    protected $fillable = [
        'name',
        'permissions',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'user_group_id', 'id');
    }

    public function sites()
    {
        return $this->morphToMany(Site::class, 'siteable');
    }
}
