<?php

namespace App\Models\Food\Foodstuff;

use Illuminate\Database\Eloquent\Model;

class FoodstuffTranslation extends Model
{
    protected $table = 'foodstuff_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
