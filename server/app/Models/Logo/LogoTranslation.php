<?php

namespace App\Models\Logo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogoTranslation extends Model
{
    protected $table = 'logo_translations';

    protected $fillable = [
        'logo_id',
        'locale',
        'url',
    ];
}
