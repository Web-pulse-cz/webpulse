<?php

namespace App\Http\Resources\Admin\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
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
            'description' => $this->description,
            'tags' => $this->tags,
            'status' => $this->status,
            'accuracy' => $this->accuracy,
            'attempts' => $this->attempts,
            'questions' => QuizQuestionResource::collection($this->whenLoaded('questions')),
            'published_at' => $this->published_at,
        ];
    }
}
