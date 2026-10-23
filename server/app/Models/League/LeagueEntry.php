<?php

namespace App\Models\League;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class LeagueEntry extends Model
{
    protected $table = 'league_entries';

    protected $fillable = [
        'league_id',
        'league_activity_id',
        'user_id',
        'date',
        'count',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'count' => 'integer',
    ];

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(LeagueActivity::class, 'league_activity_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
