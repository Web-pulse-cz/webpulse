<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqCategoryTranslation extends Model
{
    protected $table = 'faq_category_translations';

    protected $fillable = ['faq_category_id', 'locale', 'name', 'meta_title', 'meta_description'];
}
