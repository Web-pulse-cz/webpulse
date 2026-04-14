<?php

namespace App\Models\Food\Menu;

use Illuminate\Database\Eloquent\Model;

class MenuTranslation extends Model
{
    protected $table = 'menu_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
