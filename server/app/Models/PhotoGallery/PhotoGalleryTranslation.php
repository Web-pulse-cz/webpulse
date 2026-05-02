<?php

namespace App\Models\PhotoGallery;

use Illuminate\Database\Eloquent\Model;

class PhotoGalleryTranslation extends Model
{
    protected $table = 'photo_gallery_translations';

    protected $fillable = [
        'photo_gallery_id',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected $translationForeignKey = 'photo_gallery_id';
}
