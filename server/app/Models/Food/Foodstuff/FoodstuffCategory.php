<?php

namespace App\Models\Food\Foodstuff;

use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class FoodstuffCategory extends Model
{
    use Siteable, Translatable;

    protected $table = 'foodstuff_categories';

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $fillable = [
        'foodstuff_category_id',
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

    public function foodstuffCategory()
    {
        return $this->belongsTo(FoodstuffCategory::class, 'foodstuff_category_id', 'id')->with('foodstuffCategory');
    }

    public function foodstuffCategories()
    {
        return $this->hasMany(FoodstuffCategory::class, 'foodstuff_category_id', 'id');
    }
}
