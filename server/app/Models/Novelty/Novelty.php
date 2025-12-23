<?php

namespace App\Models\Novelty;

use App\Traits\Imagable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Novelty extends Model
{
    use Translatable, Imagable;

    protected $table = 'novelties';

    protected $fillable = [
        'active',
        'priority',
    ];

    protected $casts = [
        'active' => 'boolean',
        'priority' => 'integer',
    ];

    protected $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    public function getAttribute($key)
    {
        // Pokud je požadovaný atribut přeložitelný
        if (in_array($key, $this->translatedAttributes)) {
            // Pokus o získání překladu pro aktuální locale
            $translation = $this->translate(App::getLocale(), false);
            if ($translation && $translation->$key !== null) {
                return $translation->$key;
            }
            // Pokud překlad chybí, můžeme zkusit fallback
            $fallbackTranslation = $this->translate(config('app.fallback_locale'), false);
            if ($fallbackTranslation && $fallbackTranslation->$key !== null) {
                return $fallbackTranslation->$key;
            }
            // Jinak můžeš vrátit null nebo původní hodnotu
            return null;
        }

        // Jinak klasicky vrátí atribut
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
}
