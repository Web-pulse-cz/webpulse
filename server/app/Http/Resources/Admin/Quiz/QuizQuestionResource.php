<?php

namespace App\Http\Resources\Admin\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
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
            'quiz_id' => $this->quiz_id,
            'name' => $this->name,
            'image' => $this->main_image,
            'answers' => QuizAnswerResource::collection($this->whenLoaded('answers'))
        ];
    }
}
