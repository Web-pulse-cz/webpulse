<?php

namespace App\Models\League;

use Illuminate\Database\Eloquent\Model;

class LeagueSection extends Model
{
    protected $table = 'league_sections';

    protected $fillable = [
        'league_id',
        'name',
        'position',
    ];

    public function league()
    {
        return $this->belongsTo(League::class, 'league_id', 'id');
    }

    public function activities()
    {
        return $this->hasMany(LeagueActivity::class, 'league_section_id', 'id')->orderBy('position')->orderBy('id');
    }
}
