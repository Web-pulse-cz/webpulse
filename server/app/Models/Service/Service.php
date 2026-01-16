<?php

namespace App\Models\Service;

use App\Models\Currency\Currency;
use App\Models\TaxRate\TaxRate;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Service extends Model
{
    use Translatable, Siteable;

    protected $table = 'services';

    protected $fillable = [
        'type',
        'price_type',
        'price',
        'tax_rate_id',
        'currency_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
        'price_type' => 'string',
        'type' => 'string',
    ];

    protected $translatedAttributes = [
        'name',
        'locale',
        'slug',
        'perex',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected $with = ['currency', 'taxRate'];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id', 'id');
    }

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
