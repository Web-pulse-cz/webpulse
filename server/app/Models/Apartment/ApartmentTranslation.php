<?php

namespace App\Models\Apartment;

use Illuminate\Database\Eloquent\Model;

class ApartmentTranslation extends Model
{
    protected $table = 'apartment_translations';

    protected $fillable = [
        'apartment_id',
        'locale',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
