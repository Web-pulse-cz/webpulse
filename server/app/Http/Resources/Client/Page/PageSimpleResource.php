<?php

namespace App\Http\Resources\Client\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageSimpleResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
        ];
    }
}
