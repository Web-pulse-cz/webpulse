<?php

namespace App\Http\Resources\Admin\PersonalGrowth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AudioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'speaker' => $this->speaker,
            'notes' => $this->notes,
            'keywords' => $this->keywords ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
