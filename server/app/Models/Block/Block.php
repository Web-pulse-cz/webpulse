<?php

namespace App\Models\Block;

use App\Models\Site\Site;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Block extends Model
{
    use Translatable;

    protected $table = 'blocks';

    protected $fillable = [
        'site_id',
        'blockable_type',
        'blockable_id',
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

    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
