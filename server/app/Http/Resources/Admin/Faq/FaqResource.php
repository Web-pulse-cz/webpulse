<?php

namespace App\Http\Resources\Admin\Faq;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
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
            'position' => $this->position,
            'question' => $this->question,
            'answer' => $this->answer,
            'active' => $this->active,
            'categories' => $this->categories->pluck('id')->toArray(),
            'translations' => array_column($this->translations->toArray(), null, 'locale'),
            'sites' => $this->sites
        ];
    }
}
