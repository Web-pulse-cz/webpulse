<?php

namespace App\Http\Resources\Admin\League;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'league_section_id' => $this->league_section_id,
            'name' => $this->name,
            'value' => (int) $this->value,
            'max_per_day' => $this->max_per_day,
            'counts_to_league' => (bool) $this->counts_to_league,
            'position' => $this->position,
            'entries_count' => $this->entries_count ?? null,
        ];
    }
}
