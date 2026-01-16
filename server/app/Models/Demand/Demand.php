<?php

namespace App\Models\Demand;

use App\Models\Service\Service;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demand extends Model
{
    use Siteable;

    protected $table = 'demands';

    protected $fillable = [
        'fullname',
        'email',
        'phone_prefix',
        'phone',
        'url',
        'text',
        'service_id',
        'offer_price',
        'locale'
    ];

    protected $casts = [
        'offer_price' => 'decimal:2',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function getFullPhoneAttribute()
    {
        return $this->phone_prefix . trim($this->phone);
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
