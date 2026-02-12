<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    protected $table = 'post_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
