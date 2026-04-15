<?php

namespace App\Http\Resources\Admin\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTimeEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $project = $this->project;
        $hours = $this->seconds / 3600;
        $hourlyRate = $this->hourly_rate ?? 0;
        $priceWithoutVat = round($hours * $hourlyRate, 2);

        $taxRate = $project?->taxRate?->rate ?? 0;
        $vat = round($priceWithoutVat * ($taxRate / 100), 2);
        $priceWithVat = $priceWithoutVat + $vat;

        $currencySymbol = $project?->currency?->code ?? 'Kč';

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project_name' => $project?->name,
            'task_id' => $this->task_id,
            'task_name' => $this->task?->name,
            'task_code' => $this->task?->code,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'description' => $this->description,
            'seconds' => (int) $this->seconds,
            'hourly_rate' => $hourlyRate,
            'price_without_vat' => $priceWithoutVat,
            'vat' => $vat,
            'vat_rate' => $taxRate,
            'price_with_vat' => $priceWithVat,
            'currency_symbol' => $currencySymbol,
            'date' => $this->date?->format('Y-m-d'),
            'timer_started_at' => $this->timer_started_at?->toIso8601String(),
            'is_running' => $this->is_running,
        ];
    }
}
