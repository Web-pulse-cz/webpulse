<?php

namespace App\Models\Food\Meal;

use Illuminate\Database\Eloquent\Model;

class MealTranslation extends Model
{
    protected $table = 'meal_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}