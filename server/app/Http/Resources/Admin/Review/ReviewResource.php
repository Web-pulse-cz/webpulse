<?php

namespace App\Http\Resources\Admin\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'rating' => $this->rating,
            'active' => $this->active,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'name' => $this->name,
            'content' => $this->content,
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
