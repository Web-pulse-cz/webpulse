<?php

namespace App\Http\Resources\Admin\Project;

use App\Http\Resources\Admin\Client\ClientSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'client' => ClientSimpleResource::make($this->client),
            'status' => ProjectStatusResource::make($this->status),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'deadline_date' => $this->deadline_date?->format('Y-m-d'),
            'hourly_rate' => $this->hourly_rate,
            'total_tracked_seconds' => $this->total_tracked_seconds,
            'total_revenue' => $this->total_revenue,
            'total_revenue_with_vat' => round(($this->total_tracked_seconds / 3600) * ($this->hourly_rate ?? 0) * (1 + ($this->taxRate?->rate ?? 0) / 100), 2),
            'currency_symbol' => $this->currency?->code ?? 'Kč',
            'profit' => $this->profit,
            'is_archived' => $this->is_archived,
        ];
    }
}
