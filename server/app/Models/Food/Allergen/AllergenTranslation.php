<?php

namespace App\Models\Food\Allergen;

use Illuminate\Database\Eloquent\Model;

class AllergenTranslation extends Model
{
    protected $table = 'allergen_translations';

    protected $fillable = ['name', 'description'];
}
