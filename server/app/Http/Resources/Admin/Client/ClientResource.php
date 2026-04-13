<?php

namespace App\Http\Resources\Admin\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fakturoid_id' => $this->fakturoid_id,
            'type' => $this->type,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'email_copy' => $this->email_copy,
            'phone_prefix' => $this->phone_prefix,
            'phone' => $this->phone,
            'ico' => $this->ico,
            'dic' => $this->dic,
            'web' => $this->web,
            'street' => $this->street,
            'city' => $this->city,
            'zip' => $this->zip,
            'country_id' => $this->country_id,
            'has_delivery_address' => $this->has_delivery_address,
            'delivery_name' => $this->delivery_name,
            'delivery_street' => $this->delivery_street,
            'delivery_city' => $this->delivery_city,
            'delivery_zip' => $this->delivery_zip,
            'delivery_country_id' => $this->delivery_country_id,
            'bank_account_number' => $this->bank_account_number,
            'bank_account_iban' => $this->bank_account_iban,
            'bank_account_swift' => $this->bank_account_swift,
            'variable_symbol' => $this->variable_symbol,
            'note' => $this->note,
            'sites' => $this->whenLoaded('sites', fn() => $this->sites->pluck('id')),
            'synced_at' => $this->synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
