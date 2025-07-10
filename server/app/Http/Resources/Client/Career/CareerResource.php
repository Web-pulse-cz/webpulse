<?php

namespace App\Http\Resources\Client\Career;

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
            'image' => $this->image,
            'salary_from' => $this->salary_from,
            'salary_to' => $this->salary_to,
            'salary_type' => $this->salary_type,
            'start_date' => $this->start_date,
            'name' => $this->name,
            'slug' => $this->slug,
            'perex' => $this->perex,
            'text' => $this->text,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'location' => $this->location,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
        ];
    }
}
