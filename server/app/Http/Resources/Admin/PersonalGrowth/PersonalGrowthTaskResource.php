<?php

namespace App\Http\Resources\Admin\PersonalGrowth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalGrowthTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_done' => $this->is_done,
            'done_at' => $this->done_at,
            'position' => $this->position,
        ];
    }
}
