<?php

namespace App\Models\Setting;

use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Setting extends Model
{
    use Translatable, Siteable;

    protected $table = 'settings';

    protected $fillable = [
        'type',
    ];

    protected $translatedAttributes = [
        'value',
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

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
