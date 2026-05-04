<?php

namespace App\Models\Block;

use Illuminate\Database\Eloquent\Model;

class BlockTranslation extends Model
{
    protected $table = 'block_translations';

    protected $fillable = [
        'block_id',
        'locale',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
