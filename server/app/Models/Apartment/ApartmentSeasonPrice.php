<?php

namespace App\Models\Apartment;

use App\Models\Season\Season;
use Illuminate\Database\Eloquent\Model;

class ApartmentSeasonPrice extends Model
{
    protected $table = 'apartment_season_prices';

    protected $fillable = [
        'apartment_id',
        'season_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class, 'apartment_id', 'id');
    }

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id', 'id');
    }
}
