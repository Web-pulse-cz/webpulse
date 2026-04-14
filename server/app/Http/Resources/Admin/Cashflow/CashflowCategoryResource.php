<?php

namespace App\Http\Resources\Admin\Cashflow;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashflowCategoryResource extends JsonResource
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
            'name' => $this->name,
            // 'icon' => $this->icon,
            'budgets' => CashflowBudgetResource::collection($this->budgets),
            'cashflows' => CashflowResource::collection($this->cashflows),
        ];
    }
}
