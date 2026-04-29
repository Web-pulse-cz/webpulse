<?php

namespace App\Models\Filemanager;

use App\Models\Site\Site;
use Illuminate\Database\Eloquent\Model;

class Filemanager extends Model
{
    protected $table = 'filemanagers';

    protected $fillable = [
        'entity_type',
        'format',
        'width',
        'height',
        'mode',
        'crop_position',
        'path',
        'position',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'position' => 'integer',
    ];

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'filemanagers_has_sites', 'filemanager_id', 'site_id')
            ->withTimestamps();
    }
}
