<?php

namespace App\Models\Amenity;

use App\Models\Apartment\Apartment;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use Siteable, Translatable;

    protected $table = 'amenities';

    protected $fillable = [
        'icon',
        'position',
    ];

    public $translatedAttributes = [
        'name',
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
        return $this->belongsToMany(Apartment::class, 'apartments_has_amenities', 'amenity_id', 'apartment_id');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
