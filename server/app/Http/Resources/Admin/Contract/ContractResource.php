<?php

namespace App\Http\Resources\Admin\Contract;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'project_id' => $this->project_id,
            'employee_name' => $this->whenLoaded('employee', fn () => $this->employee?->full_name),
            'project_name' => $this->whenLoaded('project', fn () => $this->project?->name),
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'date_from' => $this->date_from?->format('Y-m-d'),
            'date_to' => $this->date_to?->format('Y-m-d'),
            'salary' => $this->salary,
            'salary_type' => $this->salary_type,
            'currency_id' => $this->currency_id,
            'content' => $this->content,
            'signed_by_employee' => $this->signed_by_employee,
            'signed_at' => $this->signed_at?->format('Y-m-d'),
            'terms' => $this->terms,
            'benefits' => $this->benefits,
            'vacation_days' => $this->vacation_days,
            'notice_period_days' => $this->notice_period_days,
            'note' => $this->note,
            'files' => $this->files(),
            'sites' => $this->whenLoaded('sites', fn () => $this->sites->pluck('id')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
