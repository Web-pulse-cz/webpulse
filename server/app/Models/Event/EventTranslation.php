<?php

namespace App\Models\Event;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTranslation extends Model
{
    protected $table = 'event_translations';

    protected $fillable = [
        'event_id',
        'locale',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description'
    ];
}
