<?php

namespace App\Models\League;

use App\Models\User\User;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use Siteable;

    public const SCORING_POINTS = 'points';

    public const SCORING_MINUTES = 'minutes';

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_INVITE = 'invite';

    // How many days back (excluding today) entries can be recorded
    public const RECORD_DAYS_BACK = 6;

    protected $table = 'leagues';

    protected $fillable = [
        'name',
        'description',
        'scoring_type',
        'visibility',
        'position',
    ];

    public function sections()
    {
        return $this->hasMany(LeagueSection::class, 'league_id', 'id')->orderBy('position')->orderBy('id');
    }

    public function activities()
    {
        return $this->hasMany(LeagueActivity::class, 'league_id', 'id')->orderBy('position')->orderBy('id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'league_members', 'league_id', 'user_id')->withTimestamps();
    }

    public function entries()
    {
        return $this->hasMany(LeagueEntry::class, 'league_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }

    /**
     * Leagues the user can see: public ones, own ones and ones the user was invited to.
     * Super admin (group 1) sees everything.
     */
    public function scopeVisibleFor(Builder $query, User $user): Builder
    {
        if ((int) $user->user_group_id === 1) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('visibility', self::VISIBILITY_PUBLIC)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id));
        });
    }
}
