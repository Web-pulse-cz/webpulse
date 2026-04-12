<?php

namespace App\Http\Resources\Admin\Invoice;

use App\Http\Resources\Admin\Client\ClientSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceSimpleResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'number' => $this->number,
			'subject' => $this->subject,
			'status' => $this->status,
			'document_type' => $this->document_type,
			'total' => $this->total,
			'client' => ClientSimpleResource::make($this->client),
			'issued_on' => $this->issued_on?->format('Y-m-d'),
			'due_on' => $this->due_on?->format('Y-m-d'),
			'paid_on' => $this->paid_on?->format('Y-m-d'),
		];
	}
}
