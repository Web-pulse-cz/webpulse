<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTranslation extends Model
{
    protected $table = 'service_translations';

    protected $fillable = [
        'service_id',
        'locale',
        'name',
        'slug',
        'perex',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected $translationForeignKey = 'service_id';
}
