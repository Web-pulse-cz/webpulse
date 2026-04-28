<?php

namespace App\Models\Reservation;

use App\Models\Apartment\Apartment;
use App\Models\Currency\Currency;
use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use Siteable;

    protected $table = 'apartment_reservations';

    protected $fillable = [
        'code',
        'apartment_id',
        'start_date',
        'end_date',
        'status',
        'source',
        'guest_firstname',
        'guest_lastname',
        'guest_email',
        'guest_phone',
        'number_of_guests',
        'total_price',
        'currency_id',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2',
        'number_of_guests' => 'integer',
    ];

    public function generateCode()
    {
        $code = 'RES-'.Str::upper(Str::random(8));
        if (self::where('code', $code)->exists()) {
            $this->generateCode();
        } else {
            $this->code = $code;
        }
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }
}
