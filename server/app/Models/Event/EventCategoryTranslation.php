<?php

namespace App\Models\Event;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventCategoryTranslation extends Model
{
    protected $table = 'event_category_translations';

    protected $fillable = [
        'event_category_id',
        'locale',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description'
    ];
}
