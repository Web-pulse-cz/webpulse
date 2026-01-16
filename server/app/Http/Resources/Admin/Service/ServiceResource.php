<?php

namespace App\Http\Resources\Admin\Service;

use App\Http\Resources\Admin\Currency\CurrencyResource;
use App\Http\Resources\Admin\TaxRate\TaxRateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'type' => $this->type,
            'price_type' => $this->price_type,
            'price' => $this->price,
            'tax_rate_id' => $this->tax_rate_id,
            'tax_rate' => TaxRateResource::make($this->taxRate),
            'currency_id' => $this->currency_id,
            'currency' => CurrencyResource::make($this->currency),
            'image' => $this->main_image,
            'active' => $this->active,
            'name' => $this->name,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites
        ];
    }
}
