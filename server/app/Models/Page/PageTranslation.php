<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    protected $table = 'page_translations';

    protected $fillable = [
        'page_id',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
    ];

    protected $translationForeignKey = 'page_id';
}
