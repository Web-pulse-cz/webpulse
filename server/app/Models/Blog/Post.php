<?php

namespace App\Models\Blog;

use App\Traits\Imagable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Post extends Model
{
    use Translatable, Imagable;

    protected $table = 'posts';

    protected $fillable = [
        'status',
        'published_from',
        'published_to',
    ];

    protected $casts = [
        'status' => 'string',
        'published_from' => 'datetime',
        'published_to' => 'datetime',
    ];

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    public function categories()
    {
        return $this->belongsToMany(PostCategory::class, 'posts_in_categories', 'post_id', 'post_category_id');
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


    public function getMainImageAttribute()
    {
        return $this->getMainImage($this);
    }

    public function getImagesAttribute()
    {
        return $this->imagesAttribute($this);
    }
}
