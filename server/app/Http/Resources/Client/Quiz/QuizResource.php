<?php

namespace App\Http\Resources\Client\Quiz;

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
            'accuracy' => (int)$this->accuracy,
            'attempts' => $this->attempts,
            'questions' => QuizQuestionResource::collection($this->whenLoaded('questions')),
        ];
    }
}
