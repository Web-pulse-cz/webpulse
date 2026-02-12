<?php

namespace App\Models\Activity;

use App\Models\Contact\ContactHistory;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_has_activities');
    }

    public function contactHistories()
    {
        return $this->hasMany(ContactHistory::class, 'activity_id', 'id');
    }
}
