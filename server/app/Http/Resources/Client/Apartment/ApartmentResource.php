<?php

namespace App\Http\Resources\Client\Apartment;

use App\Http\Resources\Client\Amenity\AmenityResource;
use App\Http\Resources\Client\Building\BuildingResource;
use App\Http\Resources\Client\Currency\CurrencyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'image' => $this->main_image,
            'images' => $this->images->pluck('filename')->values()->toArray(),
            'apartment_type_id' => $this->apartment_type_id,
            'building_id' => $this->building_id,
            'currency_id' => $this->currency_id,
            'capacity' => $this->capacity,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'area' => $this->area,
            'floor' => $this->floor,
            'base_price' => $this->base_price,
            'position' => $this->position,
            'apartment_type' => ApartmentTypeResource::make($this->whenLoaded('type')),
            'building' => BuildingResource::make($this->whenLoaded('building')),
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'season_prices' => $this->whenLoaded('seasonPrices', fn () => $this->seasonPrices->map(fn ($p) => [
                'season_id' => $p->season_id,
                'price' => $p->price,
            ])),
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];
    }
}
