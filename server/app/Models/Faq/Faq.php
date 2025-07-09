<?php

namespace App\Models\Faq;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Faq extends Model
{
    use Translatable;

    protected $table = 'faqs';

    protected $fillable = ['position', 'active'];

    public $translatedAttributes = ['question', 'answer'];

    protected $casts = [
        'active' => 'boolean',
        'position' => 'integer',
    ];

    public function categories()
    {
        return $this->belongsToMany(FaqCategory::class, 'faqs_in_categories', 'faq_id', 'faq_category_id');
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
}
