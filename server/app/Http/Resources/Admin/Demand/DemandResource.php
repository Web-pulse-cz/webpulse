<?php

namespace App\Http\Resources\Admin\Demand;

use App\Http\Resources\Admin\Service\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandResource extends JsonResource
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
            'fullname' => $this->fullname,
            'email' => $this->email,
            'phone' => sprintf('%s%s', $this->phone_prefix, trim($this->phone)),
            'url' => $this->url,
            'text' => $this->text,
            'service' => ServiceResource::make($this->service),
            'service_name' => $this->service ? $this->service->name : null,
            'offer_price' => $this->offer_price,
            'locale' => $this->locale,
        ];
    }
}
