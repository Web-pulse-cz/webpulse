<?php

namespace App\Models\Newsletter;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $table = 'newsletters';

    protected $fillable = [
        'email',
        'firstname',
        'lastname',
        'addressing',
        'locale',
    ];

    public function getFullNameAttribute()
    {
        return trim("{$this->firstname} {$this->lastname}");
    }
}
