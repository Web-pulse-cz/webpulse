<?php

namespace App\Http\Resources\Admin\PriceOffer;

use App\Http\Resources\Admin\Client\ClientSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceOfferSimpleResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'code' => $this->code,
			'title' => $this->title,
			'status' => $this->status,
			'client' => ClientSimpleResource::make($this->client),
			'total_with_vat' => $this->total_with_vat,
			'valid_to' => $this->valid_to?->format('Y-m-d'),
			'created_at' => $this->created_at?->toIso8601String(),
		];
	}
}
