<?php

namespace App\Models\Season;

use Illuminate\Database\Eloquent\Model;

class SeasonTranslation extends Model
{
    protected $table = 'season_translations';

    protected $fillable = [
        'season_id',
        'locale',
        'name',
    ];
}
