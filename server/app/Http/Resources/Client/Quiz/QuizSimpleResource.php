<?php

namespace App\Http\Resources\Client\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class QuizSimpleResource extends JsonResource
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
            'slug' => $this->slug,
            'tags' => $this->tags,
            'tags_array' => explode(',', $this->tags),
            'questions_count' => $this->questions->count(),
            'accuracy' => $this->accuracy,
            'attempts' => $this->attempts,
            'is_new' => Carbon::parse($this->created_at)->greaterThan(Carbon::now()->subDays(31)),
        ];
    }
}
