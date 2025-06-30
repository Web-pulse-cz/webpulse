<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingTranslation extends Model
{
    protected $table = 'setting_translations';

    protected $fillable = [
        'setting_id',
        'locale',
        'value',
    ];

    protected $casts = [
        'value' => 'array', // Assuming value is stored as JSON
    ];
}
