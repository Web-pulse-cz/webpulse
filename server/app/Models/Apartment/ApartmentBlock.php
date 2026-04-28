<?php

namespace App\Models\Apartment;

use Illuminate\Database\Eloquent\Model;

class ApartmentBlock extends Model
{
    protected $table = 'apartment_blocks';

    protected $fillable = [
        'apartment_id',
        'start_date',
        'end_date',
        'reason',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'id');
    }
}
