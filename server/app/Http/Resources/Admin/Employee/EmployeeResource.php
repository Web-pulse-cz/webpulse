<?php

namespace App\Http\Resources\Admin\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'personal_id_number' => $this->personal_id_number,
            'id_card_number' => $this->id_card_number,
            'nationality' => $this->nationality,
            'street' => $this->street,
            'city' => $this->city,
            'zip' => $this->zip,
            'country_id' => $this->country_id,
            'position' => $this->position,
            'employee_number' => $this->employee_number,
            'status' => $this->status,
            'date_hired' => $this->date_hired?->format('Y-m-d'),
            'date_terminated' => $this->date_terminated?->format('Y-m-d'),
            'hourly_rate' => $this->hourly_rate,
            'monthly_salary' => $this->monthly_salary,
            'currency_id' => $this->currency_id,
            'bank_account_number' => $this->bank_account_number,
            'bank_account_iban' => $this->bank_account_iban,
            'bank_account_swift' => $this->bank_account_swift,
            'health_insurance_company' => $this->health_insurance_company,
            'health_insurance_number' => $this->health_insurance_number,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relation' => $this->emergency_contact_relation,
            'photo' => $this->photo,
            'note' => $this->note,
            'divisions' => EmployeeDivisionResource::collection($this->whenLoaded('divisions')),
            'contracts' => EmployeeContractResource::collection($this->whenLoaded('contracts')),
            'active_contract' => EmployeeContractResource::make($this->whenLoaded('activeContract')),
            'sites' => $this->whenLoaded('sites', fn () => $this->sites->pluck('id')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
