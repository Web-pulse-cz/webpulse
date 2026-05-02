<?php

namespace App\Models\PhotoGallery;

use App\Traits\Imagable;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class PhotoGallery extends Model
{
    use Imagable, Siteable, Translatable;

    protected $table = 'photo_galleries';

    protected $fillable = [
        'active',
        'position',
    ];

    protected $casts = [
        'active' => 'boolean',
        'position' => 'integer',
    ];

    protected $translatedAttributes = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
    ];

    public function getAttribute($key)
    {
        if (in_array($key, $this->translatedAttributes)) {
            $translation = $this->translate(App::getLocale(), false);
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
