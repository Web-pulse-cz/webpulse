<?php

namespace App\Models\Food\Foodstuff;

use Illuminate\Database\Eloquent\Model;

class FoodstuffCategoryTranslation extends Model
{
    protected $table = 'foodstuff_category_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
