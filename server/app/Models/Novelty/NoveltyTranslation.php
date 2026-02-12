<?php

namespace App\Models\Novelty;

use Illuminate\Database\Eloquent\Model;

class NoveltyTranslation extends Model
{
    protected $table = 'novelty_translations';

    protected $fillable = [
        'novelty_id',
        'locale',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
