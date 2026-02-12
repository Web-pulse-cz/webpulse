<?php

namespace App\Models\Event;

use App\Models\Currency\Currency;
use App\Models\TaxRate\TaxRate;
use App\Traits\Imagable;
use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use Translatable, Imagable, Siteable;

    protected $table = 'events';

    protected $fillable = [
        'code',
        'status',
        'position',
        'place',
        'is_online',
        'max_participants',
        'registration_required',
        'price',
        'registration_from',
        'start_date',
        'end_date',
        'event_category_id',
        'currency_id',
        'tax_rate_id'
    ];

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description'
    ];

    protected $casts = [
        'registration_from' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'decimal:2',
        'max_participants' => 'integer',
        'registration_required' => 'boolean',
        'is_online' => 'boolean',
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

    public function generateCode()
    {
        //$this->code = 'EVT-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        $code = Str::random(8);
        if(self::where('code', $code)->exists()) {
            $this->generateCode(); // Recursively generate a new code if the current one already exists
        } else {
            $this->code = $code;
        }
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id', 'id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'id');
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
