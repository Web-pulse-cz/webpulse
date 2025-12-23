<?php

namespace App\Http\Resources\Admin\Career;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerResource extends JsonResource
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
            'name' => $this->name,
            'position' => $this->position,
            'image' => $this->main_image,
            'type' => $this->type,
            'status' => $this->status,
            'salary_from' => $this->salary_from,
            'salary_to' => $this->salary_to,
            'salary_type' => $this->salary_type,
            'start_date' => $this->start_date,
            'active' => $this->status === 'open',
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'application_count' => $this->applications->count(),
            'applications' => [
                'data' => CareerApplicationResource::collection($this->applications),
            ]
        ];
    }
}
