<?php

namespace App\Models\Food\Recipe;

use Illuminate\Database\Eloquent\Model;

class RecipeTranslation extends Model
{
    protected $table = 'recipe_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}