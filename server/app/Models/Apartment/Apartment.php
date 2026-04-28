<?php

namespace App\Models\Apartment;

use App\Models\Amenity\Amenity;
use App\Models\Building\Building;
use App\Models\Currency\Currency;
use App\Models\Reservation\Reservation;
use App\Models\Season\Season;
use App\Traits\Imagable;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Apartment extends Model
{
    use Imagable, Siteable, Translatable;

    protected $table = 'apartments';

    protected $fillable = [
        'code',
        'image',
        'status',
        'apartment_type_id',
        'building_id',
        'currency_id',
        'capacity',
        'bedrooms',
        'bathrooms',
        'area',
        'floor',
        'base_price',
        'position',
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
        'base_price' => 'decimal:2',
        'area' => 'decimal:2',
        'capacity' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'floor' => 'integer',
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

    public function generateCode()
    {
        $code = Str::random(8);
        if (self::where('code', $code)->exists()) {
            $this->generateCode();
        } else {
            $this->code = $code;
        }
    }

    public function type()
    {
        return $this->belongsTo(ApartmentType::class, 'apartment_type_id', 'id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'apartments_has_amenities', 'apartment_id', 'amenity_id');
    }

    public function seasons()
    {
        return $this->belongsToMany(Season::class, 'apartment_season_prices', 'apartment_id', 'season_id')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function seasonPrices()
    {
        return $this->hasMany(ApartmentSeasonPrice::class, 'apartment_id', 'id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'apartment_id', 'id');
    }

    public function blocks()
    {
        return $this->hasMany(ApartmentBlock::class, 'apartment_id', 'id');
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
