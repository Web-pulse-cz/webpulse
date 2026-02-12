<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Message\MessageBlueprint;
use App\Models\QuickAccess\QuickAccess;
use App\Models\Site\Site;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'avatar',
        'email',
        'password',
        'phone_prefix',
        'phone',
        'email_verified_at',
        'invitation_token',
        'street',
        'city',
        'zip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $with = ['quickAccess', 'userGroup'];

    public function quickAccess()
    {
        return $this->hasMany(QuickAccess::class, 'user_id', 'id');
    }

    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }

    public function messages()
    {
        return $this->hasMany(MessageBlueprint::class, 'user_id', 'id');
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'sites_has_users', 'user_id');
    }
}
