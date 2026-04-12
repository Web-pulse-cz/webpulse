<?php

namespace App\Models\Food\Recipe;

use Illuminate\Database\Eloquent\Model;

class RecipeCategoryTranslation extends Model
{
    protected $table = 'recipe_category_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}