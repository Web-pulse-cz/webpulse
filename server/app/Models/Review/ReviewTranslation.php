<?php

namespace App\Models\Review;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewTranslation extends Model
{
    protected $table = 'review_translations';
    protected $fillable = [
        'review_id',
        'locale',
        'name',
        'content',
    ];
}
