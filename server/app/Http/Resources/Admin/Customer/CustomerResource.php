<?php

namespace App\Http\Resources\Admin\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'first_name' => $this->first_name,
			'last_name' => $this->last_name,
			'full_name' => $this->full_name,
			'email' => $this->email,
			'phone_prefix' => $this->phone_prefix,
			'phone' => $this->phone,
			'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
			'gender' => $this->gender,
			'street' => $this->street,
			'city' => $this->city,
			'zip' => $this->zip,
			'country_id' => $this->country_id,
			'company_name' => $this->company_name,
			'ico' => $this->ico,
			'dic' => $this->dic,
			'total_spent' => $this->total_spent,
			'credit_balance' => $this->credit_balance,
			'currency_id' => $this->currency_id,
			'rating' => $this->rating,
			'customer_group_id' => $this->customer_group_id,
			'group' => CustomerGroupResource::make($this->whenLoaded('group')),
			'status' => $this->status,
			'note' => $this->note,
			'last_purchase_at' => $this->last_purchase_at?->toIso8601String(),
			'vouchers' => $this->whenLoaded('vouchers', function () {
				return $this->vouchers->map(fn($v) => [
					'id' => $v->id,
					'code' => $v->code,
					'name' => $v->name,
					'times_used' => $v->pivot->times_used,
					'last_used_at' => $v->pivot->last_used_at,
				]);
			}),
			'sites' => $this->whenLoaded('sites', fn() => $this->sites->pluck('id')),
			'created_at' => $this->created_at?->toIso8601String(),
		];
	}
}
