<?php

namespace App\Http\Resources\Client\Event;

use App\Http\Resources\Client\Currency\CurrencyResource;
use App\Http\Resources\Client\TaxRate\TaxRateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'image' => $this->main_image,
            'place' => $this->place,
            'is_online' => $this->is_online,
            'max_participants' => $this->max_participants,
            'registration_required' => $this->registration_required,
            'price' => $this->price,
            'registration_from' => $this->registration_from,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'category' => EventCategoryResource::make($this->category),
            'currency' => CurrencyResource::make($this->currency),
            'tax_rate' => TaxRateResource::make($this->taxRate),
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];
    }
}
