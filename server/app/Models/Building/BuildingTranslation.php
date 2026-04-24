<?php

namespace App\Models\Building;

use Illuminate\Database\Eloquent\Model;

class BuildingTranslation extends Model
{
    protected $table = 'building_translations';

    protected $fillable = [
        'building_id',
        'locale',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
