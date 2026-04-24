<?php

namespace App\Models\Building;

use App\Models\Apartment\Apartment;
use App\Traits\Imagable;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use Imagable, Siteable, Translatable;

    protected $table = 'buildings';

    protected $fillable = [
        'image',
        'position',
        'address_street',
        'address_city',
        'address_zip',
        'country_id',
        'latitude',
        'longitude',
        'contact_name',
        'contact_email',
        'contact_phone',
    ];

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
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
        return $this->hasMany(Apartment::class, 'building_id', 'id');
    }

    public function getMainImageAttribute()
    {
        return $this->getMainImage($this);
    }

    public function getImagesAttribute()
    {
        return $this->imagesAttribute($this);
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
