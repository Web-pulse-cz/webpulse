<?php

namespace App\Http\Resources\Admin\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unit_name' => $this->unit_name,
            'unit_price' => $this->unit_price,
            'vat_rate' => $this->vat_rate,
            'total_without_vat' => $this->total_without_vat,
            'total_vat' => $this->total_vat,
            'total_with_vat' => $this->total_with_vat,
            'position' => $this->position,
        ];
    }
}
