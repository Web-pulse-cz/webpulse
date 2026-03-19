<?php

namespace App\Http\Resources\Admin\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'description' => $this->description,
            'color' => $this->color,
            'is_business' => $this->is_business,
            'is_personal' => $this->is_personal,
            'updated_at' => $this->updated_at,
        ];
    }
}
