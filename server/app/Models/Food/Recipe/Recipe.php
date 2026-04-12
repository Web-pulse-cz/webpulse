<?php

namespace App\Models\Food\Recipe;

use App\Models\Food\Allergen\Allergen;
use App\Models\Food\Foodstuff\Foodstuff;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use Translatable, Siteable;

    protected $table = 'recipes';

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'difficulty',
        'time_to_prepare',
    ];

    protected $casts = [
        'time_to_prepare' => 'integer',
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
        return $this->belongsToMany(Allergen::class, 'allergen_recipe');
    }

    public function foodstuffs()
    {
        return $this->belongsToMany(Foodstuff::class, 'foodstuff_recipe')->withPivot('quantity', 'unit');
    }

    public function categories()
    {
        return $this->belongsToMany(RecipeCategory::class, 'recipe_category_recipe');
    }
}