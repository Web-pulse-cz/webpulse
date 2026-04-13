<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerGroupResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'color' => $this->color,
			'discount_type' => $this->discount_type,
			'discount_value' => $this->discount_value,
			'discount_currency_id' => $this->discount_currency_id,
			'position' => $this->position,
			'customers_count' => $this->whenCounted('customers'),
			'sites' => $this->whenLoaded('sites', fn() => $this->sites->pluck('id')),
		];
	}
}
