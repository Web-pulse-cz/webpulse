<?php

namespace App\Models\League;

use Illuminate\Database\Eloquent\Model;

class LeagueActivity extends Model
{
    protected $table = 'league_activities';

    protected $fillable = [
        'league_id',
        'league_section_id',
        'name',
        'description',
        'value',
        'max_per_day',
        'counts_to_league',
        'position',
    ];

    protected $casts = [
        'value' => 'integer',
        'max_per_day' => 'integer',
        'counts_to_league' => 'boolean',
    ];

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(LeagueSection::class, 'league_section_id', 'id');
    }

    public function entries()
    {
        return $this->hasMany(LeagueEntry::class, 'league_activity_id', 'id');
    }
}
