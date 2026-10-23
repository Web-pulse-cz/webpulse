<?php

namespace App\Http\Resources\Admin\League;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'activities' => LeagueActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}
