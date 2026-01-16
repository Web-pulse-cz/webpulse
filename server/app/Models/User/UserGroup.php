<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;

    public $table = 'user_groups';

    protected $fillable = [
        'name',
        'permissions'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'user_group_id', 'id');
    }
}
