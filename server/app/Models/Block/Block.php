<?php

namespace App\Models\Block;

use App\Traits\Siteable;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use Siteable, Translatable;

    protected $table = 'blocks';

    protected $fillable = [
        'type',
        'data',
        'position',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    public $translatedAttributes = [];

    public $translationModel = BlockTranslation::class;

    public $translationForeignKey = 'block_id';

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
