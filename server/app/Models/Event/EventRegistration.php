<?php

namespace App\Models\Event;

use App\Models\Country\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    protected $table = 'event_registrations';

    protected $fillable = [
        'event_id',
        'email',
        'firstname',
        'lastname',
        'phone',
        'note',
        'ico',
        'dic',
        'company',
        'street',
        'city',
        'zip',
        'country_id',
        'is_paid'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}
