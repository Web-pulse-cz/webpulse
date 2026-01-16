<?php

namespace App\Http\Resources\Admin\Site;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
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
            'hash' => $this->hash,
            'is_secure' => $this->is_secure,
            'is_active' => $this->is_active,
            'settings' => $this->settings,
            'users' => $this->users,
        ];
    }
}
