<?php

namespace App\Models\Restaurant;

use App\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
	protected $table = 'reservations';

	protected $fillable = [
		'table_id', 'date', 'time_from', 'time_to',
		'guest_first_name', 'guest_last_name',
		'guest_phone_prefix', 'guest_phone', 'guest_email',
		'guests_count', 'customer_id',
		'status', 'source', 'note',
	];

	protected $casts = [
		'date' => 'date',
	];

	protected static function booted(): void
	{
		// Auto-match customer on save
		static::saving(function (Reservation $reservation) {
			if (!$reservation->customer_id) {
				$reservation->customer_id = static::matchCustomer($reservation);
			}
		});

		// Refresh table status after reservation changes
		static::saved(function (Reservation $reservation) {
			$reservation->restaurantTable?->refreshStatus();
		});

		static::deleted(function (Reservation $reservation) {
			$reservation->restaurantTable?->refreshStatus();
		});
	}

	public function restaurantTable()
	{
		return $this->belongsTo(RestaurantTable::class, 'table_id', 'id');
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class, 'customer_id', 'id');
	}

	public function getGuestFullNameAttribute(): string
	{
		return $this->guest_first_name . ' ' . $this->guest_last_name;
	}

	public function getIsRegisteredCustomerAttribute(): bool
	{
		return $this->customer_id !== null;
	}

	/**
	 * Try to match reservation guest to an existing customer by name + phone.
	 */
	protected static function matchCustomer(Reservation $reservation): ?int
	{
		if (!$reservation->guest_first_name || !$reservation->guest_last_name) {
			return null;
		}

		$query = Customer::where('first_name', $reservation->guest_first_name)
			->where('last_name', $reservation->guest_last_name);

		if ($reservation->guest_phone) {
			$query->where('phone', $reservation->guest_phone);
		}

		return $query->first()?->id;
	}
}
