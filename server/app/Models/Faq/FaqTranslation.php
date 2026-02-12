<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqTranslation extends Model
{
    protected $table = 'faq_translations';

    protected $fillable = ['faq_id', 'locale', 'question', 'answer'];
}
