<?php

namespace App\Http\Resources\Admin\Voucher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'currency_id' => $this->currency_id,
            'min_order_value' => $this->min_order_value,
            'valid_from' => $this->valid_from?->format('Y-m-d'),
            'valid_to' => $this->valid_to?->format('Y-m-d'),
            'max_uses' => $this->max_uses,
            'used_count' => $this->used_count,
            'max_uses_per_customer' => $this->max_uses_per_customer,
            'is_active' => $this->is_active,
            'is_valid' => $this->isValid(),
            'customers' => $this->whenLoaded('customers', function () {
                return $this->customers->map(fn ($c) => [
                    'id' => $c->id,
                    'full_name' => $c->full_name,
                    'email' => $c->email,
                    'times_used' => $c->pivot->times_used,
                ]);
            }),
            'sites' => $this->whenLoaded('sites', fn () => $this->sites->pluck('id')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
