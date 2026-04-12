<?php

namespace App\Http\Resources\Admin\Food\Menu;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'menu_id' => $this->menu_id,
			'section_id' => $this->section_id,
			'section_name' => $this->section?->name,
			'meal_id' => $this->meal_id,
			'meal_name' => $this->meal?->name,
			'name' => $this->name,
			'description' => $this->description,
			'price' => $this->price,
			'weight' => $this->weight,
			'allergen_ids' => $this->allergen_ids ?? [],
			'position' => $this->position,
		];
	}
}
