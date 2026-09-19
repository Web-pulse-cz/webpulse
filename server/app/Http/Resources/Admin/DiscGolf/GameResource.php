<?php

namespace App\Http\Resources\Admin\DiscGolf;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'course_layout_id' => $this->course_layout_id,
            'custom_course_name' => $this->custom_course_name,
            'custom_layout_name' => $this->custom_layout_name,
            'course_name' => $this->course_name,
            'layout_name' => $this->layout_name,
            'played_at' => $this->played_at,
            'status' => $this->status,
            'count_to_cup' => $this->count_to_cup,
            'note' => $this->note,
            'par' => $this->par,
            'holes_count' => $this->holes_count,
            'position' => $this->position,
            'course' => CourseResource::make($this->whenLoaded('course')),
            'layout' => CourseLayoutResource::make($this->whenLoaded('layout')),
            'holes' => GameHoleResource::collection($this->whenLoaded('holes')),
            'players' => GamePlayerResource::collection($this->whenLoaded('players')),
            'players_count' => $this->players_count
                ?? ($this->relationLoaded('players') ? $this->players->count() : 0),
            'winner' => $this->whenLoaded('players', fn () => $this->winner ? [
                'player_id' => $this->winner->player_id,
                'player_name' => $this->winner->player?->name,
                'net_score' => $this->winner->net_score,
                'relative_to_par' => $this->winner->relative_to_par,
            ] : null),
            'winner_name' => $this->whenLoaded('players', fn () => $this->winner?->player?->name),
            'sites' => $this->sites,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
