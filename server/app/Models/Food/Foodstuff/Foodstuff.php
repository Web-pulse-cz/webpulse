<?php

namespace App\Models\Food\Foodstuff;

use App\Models\Food\Allergen\Allergen;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Foodstuff extends Model
{
    use Translatable, Siteable;

    protected $table = 'foodstuffs';

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'macronutrients',
    ];

    protected $casts = [
        'macronutrients' => 'array',
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

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }

    public function allergens()
    {
        return $this->belongsToMany(Allergen::class, 'allergen_foodstuff');
    }

    public function categories()
    {
        return $this->belongsToMany(FoodstuffCategory::class, 'foodstuff_category_foodstuff');
    }
}