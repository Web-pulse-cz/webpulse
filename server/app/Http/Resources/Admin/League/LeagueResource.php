<?php

namespace App\Http\Resources\Admin\League;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeagueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'scoring_type' => $this->scoring_type,
            'visibility' => $this->visibility,
            'position' => $this->position,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => trim($this->creator->firstname.' '.$this->creator->lastname),
            ] : null),
            'sections' => LeagueSectionResource::collection($this->whenLoaded('sections')),
            // Activities without a section
            'activities' => LeagueActivityResource::collection($this->whenLoaded('activities', fn () => $this->activities->whereNull('league_section_id')->values())),
            'members' => $this->whenLoaded('members', fn () => $this->members->pluck('id')->values()),
            'activities_count' => $this->activities_count ?? null,
            'members_count' => $this->members_count ?? null,
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
