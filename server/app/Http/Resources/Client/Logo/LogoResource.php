<?php

namespace App\Http\Resources\Client\Logo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogoResource extends JsonResource
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
            'url' => $this->url,
            'image' => $this->main_image,
        ];
    }
}
