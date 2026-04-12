<?php

namespace App\Models\Food\Meal;

use App\Models\Food\Allergen\Allergen;
use App\Models\Food\Foodstuff\Foodstuff;
use App\Models\Food\Menu\Menu;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use Translatable, Siteable;

    protected $table = 'meals';

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'price',
        'weight',
        'recipe_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
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
        return $this->belongsToMany(Allergen::class, 'allergen_meal');
    }

    public function foodstuffs()
    {
        return $this->belongsToMany(Foodstuff::class, 'foodstuff_meal');
    }

    public function categories()
    {
        return $this->belongsToMany(MealCategory::class, 'meal_category_meal');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'meal_menu');
    }

    public function recipe()
    {
        return $this->belongsTo(\App\Models\Food\Recipe\Recipe::class, 'recipe_id', 'id');
    }
}