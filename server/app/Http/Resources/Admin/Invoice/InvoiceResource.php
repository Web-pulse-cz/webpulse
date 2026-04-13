<?php

namespace App\Http\Resources\Admin\Invoice;

use App\Http\Resources\Admin\Client\ClientSimpleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fakturoid_id' => $this->fakturoid_id,
            'client_id' => $this->client_id,
            'client' => ClientSimpleResource::make($this->whenLoaded('client', $this->client)),
            'project_id' => $this->project_id,
            'price_offer_id' => $this->price_offer_id,
            'document_type' => $this->document_type,
            'number' => $this->number,
            'subject' => $this->subject,
            'note' => $this->note,
            'footer_note' => $this->footer_note,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'total_vat' => $this->total_vat,
            'currency_id' => $this->currency_id,
            'language_id' => $this->language_id,
            'payment_method' => $this->payment_method,
            'variable_symbol' => $this->variable_symbol,
            'constant_symbol' => $this->constant_symbol,
            'specific_symbol' => $this->specific_symbol,
            'bank_account' => $this->bank_account,
            'iban' => $this->iban,
            'swift_bic' => $this->swift_bic,
            'issued_on' => $this->issued_on?->format('Y-m-d'),
            'taxable_fulfillment_due' => $this->taxable_fulfillment_due?->format('Y-m-d'),
            'due_on' => $this->due_on?->format('Y-m-d'),
            'paid_on' => $this->paid_on?->format('Y-m-d'),
            'cancelled_on' => $this->cancelled_on?->format('Y-m-d'),
            'sent_on' => $this->sent_on?->format('Y-m-d'),
            'items' => InvoiceItemResource::collection($this->items),
            'synced_at' => $this->synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
