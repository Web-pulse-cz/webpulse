<?php

namespace App\Models\DiscGolf;

use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use Siteable;

    protected $table = 'games';

    protected $fillable = [
        'course_id',
        'course_layout_id',
        'custom_course_name',
        'custom_layout_name',
        'played_at',
        'status',
        'count_to_cup',
        'note',
        'par',
        'holes_count',
        'position',
    ];

    protected $casts = [
        'played_at' => 'datetime',
        'count_to_cup' => 'boolean',
        'par' => 'integer',
        'holes_count' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function layout()
    {
        return $this->belongsTo(CourseLayout::class, 'course_layout_id', 'id');
    }

    public function holes()
    {
        return $this->hasMany(GameHole::class, 'game_id', 'id')->orderBy('number');
    }

    public function players()
    {
        return $this->hasMany(GamePlayer::class, 'game_id', 'id')->orderBy('position');
    }

    public function holeScores()
    {
        return $this->hasMany(GameHoleScore::class, 'game_id', 'id');
    }

    /**
     * Display name of the course (real or custom one-off).
     */
    public function getCourseNameAttribute(): ?string
    {
        return $this->course?->name ?? $this->custom_course_name;
    }

    /**
     * Display name of the layout (real or custom one-off).
     */
    public function getLayoutNameAttribute(): ?string
    {
        return $this->layout?->name ?? $this->custom_layout_name;
    }

    /**
     * Winner = player with the lowest net score (throws − handicap). Requires players loaded.
     */
    public function getWinnerAttribute(): ?GamePlayer
    {
        if (! $this->relationLoaded('players') || $this->players->isEmpty()) {
            return null;
        }

        return $this->players->sortBy(fn ($gp) => $gp->net_score)->first();
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
