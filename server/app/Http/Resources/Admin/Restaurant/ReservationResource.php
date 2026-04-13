<?php

namespace App\Http\Resources\Admin\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'table_id' => $this->table_id,
			'table_number' => $this->restaurantTable?->number,
			'table_name' => $this->restaurantTable?->name,
			'date' => $this->date?->format('Y-m-d'),
			'time_from' => $this->time_from,
			'time_to' => $this->time_to,
			'guest_first_name' => $this->guest_first_name,
			'guest_last_name' => $this->guest_last_name,
			'guest_full_name' => $this->guest_full_name,
			'guest_phone_prefix' => $this->guest_phone_prefix,
			'guest_phone' => $this->guest_phone,
			'guest_email' => $this->guest_email,
			'guests_count' => $this->guests_count,
			'customer_id' => $this->customer_id,
			'is_registered_customer' => $this->is_registered_customer,
			'customer_name' => $this->customer?->full_name,
			'status' => $this->status,
			'source' => $this->source,
			'note' => $this->note,
			'created_at' => $this->created_at?->toIso8601String(),
		];
	}
}
