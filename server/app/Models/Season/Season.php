<?php

namespace App\Models\Season;

use App\Models\Apartment\Apartment;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use Siteable, Translatable;

    protected $table = 'seasons';

    protected $fillable = [
        'is_recurring',
        'start_month',
        'start_day',
        'end_month',
        'end_day',
        'start_date',
        'end_date',
        'color',
        'position',
    ];

    public $translatedAttributes = [
        'name',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_month' => 'integer',
        'start_day' => 'integer',
        'end_month' => 'integer',
        'end_day' => 'integer',
    ];

    public function getAttribute($key)
    {
        if (in_array($key, $this->translatedAttributes)) {
            $translation = $this->translate(app()->getLocale(), false);
            if ($translation && $translation->$key !== null) {
                return $translation->$key;
            }
            $fallbackTranslation = $this->translate(config('app.fallback_locale'), false);
            if ($fallbackTranslation && $fallbackTranslation->$key !== null) {
                return $fallbackTranslation->$key;
            }

            return null;
        }

        return parent::getAttribute($key);
    }

    public function apartments()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_season_prices', 'season_id', 'apartment_id')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
