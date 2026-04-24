<?php

namespace App\Models\Amenity;

use Illuminate\Database\Eloquent\Model;

class AmenityTranslation extends Model
{
    protected $table = 'amenity_translations';

    protected $fillable = [
        'amenity_id',
        'locale',
        'name',
    ];
}
