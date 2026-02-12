<?php

namespace App\Models\Currency;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Support\Facades\App;

class Currency extends Model implements TranslatableContract
{
    use Translatable;

    // init basic model
    protected $table = 'currencies';

    protected $fillable = [
        'code',
        'rate',
        'decimals',
        'active',
        'bank_account_number',
        'bank_account_name',
        'bank_account_iban',
        'bank_account_swift',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // init translation model
    public $translatedAttributes = [
        'name',
        'symbol_before',
        'symbol_after',
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

    public function convertToBase(float $amount): float
    {
        // Pokud je kurz 0, vyhoď výjimku (dělení nulou by dalo chybu)
        if ($this->rate == 0) {
            throw new \InvalidArgumentException("Currency rate cannot be zero.");
        }

        // Převod z měny na CZK = amount * rate
        return $amount * $this->rate;
    }

}
