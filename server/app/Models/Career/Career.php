<?php

namespace App\Models\Career;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Career extends Model
{
    use Translatable;

    protected $table = 'careers';

    protected $fillable = [
        'position',
        'type',
        'status',
        'salary_from',
        'salary_to',
        'salary_type',
        'start_date',
    ];

    public $translatedAttributes = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
        'location',
        'requirements',
        'benefits',
    ];

    protected $casts = [
        'position' => 'integer',
        'type' => 'string',
        'status' => 'string',
        'salary_from' => 'decimal:2',
        'salary_to' => 'decimal:2',
        'salary_type' => 'string',
        'start_date' => 'string',
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

    public function applications()
    {
        return $this->hasMany(CareerApplication::class, 'career_id', 'id');
    }
}
