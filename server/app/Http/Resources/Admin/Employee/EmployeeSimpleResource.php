<?php

namespace App\Http\Resources\Admin\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSimpleResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'first_name' => $this->first_name,
			'last_name' => $this->last_name,
			'full_name' => $this->full_name,
			'email' => $this->email,
			'phone' => $this->phone,
			'position' => $this->position,
			'status' => $this->status,
			'photo' => $this->photo,
			'date_hired' => $this->date_hired?->format('Y-m-d'),
		];
	}
}
