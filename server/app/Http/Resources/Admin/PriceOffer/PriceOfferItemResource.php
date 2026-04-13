<?php

namespace App\Http\Resources\Admin\PriceOffer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceOfferItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_name' => $this->unit_name,
            'unit_price_without_vat' => $this->unit_price_without_vat,
            'vat_rate' => $this->vat_rate,
            'total_without_vat' => $this->total_without_vat,
            'total_vat' => $this->total_vat,
            'total_with_vat' => $this->total_with_vat,
            'position' => $this->position,
        ];
    }
}
