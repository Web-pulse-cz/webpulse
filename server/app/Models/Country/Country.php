<?php

namespace App\Models\Country;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Country extends Model implements TranslatableContract
{
    use Translatable;

    protected $table = 'countries';

    protected $fillable = [
        'code',
        'iso',
        'active',
        'phone_prefix',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $translatedAttributes = [
        'name',
    ];
}
