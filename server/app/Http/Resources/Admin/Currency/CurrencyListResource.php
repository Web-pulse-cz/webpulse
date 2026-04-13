<?php

namespace App\Http\Resources\Admin\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyListResource extends JsonResource
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
            'code' => $this->code,
            'rate' => $this->rate,
            'decimals' => $this->decimals,
            'active' => $this->active,
            'name' => $this->name,
            'symbol_before' => $this->symbol_before,
            'symbol_after' => $this->symbol_after,
        ];
    }
}
