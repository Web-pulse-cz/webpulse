<?php

namespace App\Http\Resources\Admin\Changelog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChangelogResource extends JsonResource
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
            'version' => $this->version,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'type' => $this->type,
            'updated_at' => $this->updated_at
        ];
    }
}
