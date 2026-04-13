<?php

namespace App\Http\Resources\Admin\PriceOffer;

use App\Http\Resources\Admin\Client\ClientSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'user_id' => $this->user_id,
            'client_id' => $this->client_id,
            'client' => ClientSimpleResource::make($this->client),
            'project_id' => $this->project_id,
            'title' => $this->title,
            'introduction' => $this->introduction,
            'note' => $this->note,
            'terms' => $this->terms,
            'status' => $this->status,
            'currency_id' => $this->currency_id,
            'tax_rate_id' => $this->tax_rate_id,
            'total_without_vat' => $this->total_without_vat,
            'total_vat' => $this->total_vat,
            'total_with_vat' => $this->total_with_vat,
            'valid_to' => $this->valid_to?->format('Y-m-d'),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'invoice_id' => $this->invoice_id,
            'items' => PriceOfferItemResource::collection($this->items),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
