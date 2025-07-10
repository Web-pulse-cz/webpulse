<?php

namespace App\Models\Career;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerTranslation extends Model
{
    protected $table = 'career_translations';

    protected $fillable = [
        'career_id',
        'locale',
        'name',
        'slug',
        'perex',
        'text',
        'meta_title',
        'meta_description',
        'location',
        'requirements',
        'benefits',
    ];
}
