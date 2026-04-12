<?php

namespace App\Models\Food\Allergen;

use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    use Translatable, Siteable;

    protected $table = 'allergens';

    public $translatedAttributes = ['name', 'description'];

    protected $fillable = [
        'number'
    ];

    protected $casts = [
        'number' => 'integer',
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
}
