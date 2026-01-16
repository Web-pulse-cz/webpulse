<?php

namespace App\Models\Newsletter;

use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use Siteable;

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

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
