<?php

namespace App\Http\Resources\Admin\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'date' => $this->date?->format('Y-m-d'),
            'invoice_number' => $this->invoice_number,
        ];
    }
}
