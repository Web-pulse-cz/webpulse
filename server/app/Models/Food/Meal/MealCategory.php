<?php

namespace App\Models\Food\Meal;

use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class MealCategory extends Model
{
    use Translatable, Siteable;

    protected $table = 'meal_categories';

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'meal_category_id',
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

    public function mealCategory()
    {
        return $this->belongsTo(MealCategory::class, 'meal_category_id', 'id')->with('mealCategory');
    }

    public function mealCategories()
    {
        return $this->hasMany(MealCategory::class, 'meal_category_id', 'id');
    }
}