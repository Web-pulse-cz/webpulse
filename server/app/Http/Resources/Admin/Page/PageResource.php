<?php

namespace App\Http\Resources\Admin\Page;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
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
            'active' => $this->active,
            'name' => $this->name,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites
        ];
    }
}
