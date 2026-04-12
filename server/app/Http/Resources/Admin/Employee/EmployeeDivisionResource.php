<?php

namespace App\Http\Resources\Admin\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDivisionResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description,
			'color' => $this->color,
			'address' => $this->address,
			'city' => $this->city,
			'zip' => $this->zip,
			'phone' => $this->phone,
			'email' => $this->email,
			'head_employee_id' => $this->head_employee_id,
			'head_employee_name' => $this->headEmployee?->full_name,
			'position' => $this->position,
			'employees_count' => $this->whenCounted('employees'),
		];
	}
}
