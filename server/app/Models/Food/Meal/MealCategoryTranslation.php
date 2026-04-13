<?php

namespace App\Models\Food\Meal;

use Illuminate\Database\Eloquent\Model;

class MealCategoryTranslation extends Model
{
    protected $table = 'meal_category_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
