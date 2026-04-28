<?php

namespace App\Models\Apartment;

use Illuminate\Database\Eloquent\Model;

class ApartmentTypeTranslation extends Model
{
    protected $table = 'apartment_type_translations';

    protected $fillable = [
        'apartment_type_id',
        'locale',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
