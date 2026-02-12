<?php

namespace App\Http\Resources\Admin\Cashflow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashflowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cashflow_category_id' => $this->category ? $this->category->id : null,
            'amount' => (float)$this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'date' => $this->date->format('Y-m-d'),
            'is_repeated' => $this->is_repeated
        ];
    }
}
