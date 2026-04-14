<?php

namespace App\Models\Restaurant;

use App\Traits\Siteable;
use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    use Siteable;

    protected $table = 'restaurant_tables';

    protected $fillable = [
        'number', 'name', 'seats', 'location',
        'description', 'status', 'position',
    ];

    public function sites()
    {
        return $this->morphToMany('App\Models\Site\Site', 'siteable');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'table_id', 'id');
    }

    public function upcomingReservations()
    {
        return $this->hasMany(Reservation::class, 'table_id', 'id')
            ->where('date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'no_show', 'completed'])
            ->orderBy('date')->orderBy('time_from');
    }

    public function todayReservations()
    {
        return $this->hasMany(Reservation::class, 'table_id', 'id')
            ->where('date', now()->toDateString())
            ->orderBy('time_from');
    }

    /**
     * Auto-update status based on current reservations.
     */
    public function refreshStatus(): void
    {
        if ($this->status === 'maintenance') {
            return;
        }

        $now = now();
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        $soonTime = $now->copy()->addMinutes(30)->format('H:i:s');

        // Check for seated reservation (guest is at the table right now)
        $seatedReservation = Reservation::where('table_id', $this->id)
            ->where('date', $today)
            ->where('status', 'seated')
            ->first();

        if ($seatedReservation) {
            $this->status = 'occupied';
            $this->saveQuietly();

            return;
        }

        // Check for confirmed reservation happening right now
        $currentConfirmed = Reservation::where('table_id', $this->id)
            ->where('date', $today)
            ->where('time_from', '<=', $currentTime)
            ->where(function ($q) use ($currentTime) {
                $q->whereNull('time_to')
                    ->orWhere('time_to', '>=', $currentTime);
            })
            ->where('status', 'confirmed')
            ->first();

        if ($currentConfirmed) {
            $this->status = 'reserved';
            $this->saveQuietly();

            return;
        }

        // Check for upcoming reservation in the next 30 minutes
        $upcomingSoon = Reservation::where('table_id', $this->id)
            ->where('date', $today)
            ->where('time_from', '>', $currentTime)
            ->where('time_from', '<=', $soonTime)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        $this->status = $upcomingSoon ? 'reserved' : 'available';
        $this->saveQuietly();
    }
}
