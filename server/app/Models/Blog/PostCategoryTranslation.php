<?php

namespace App\Models\Blog;

use Illuminate\Database\Eloquent\Model;

class PostCategoryTranslation extends Model
{
    protected $table = 'post_category_translations';

    protected $fillable = [
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];
}
