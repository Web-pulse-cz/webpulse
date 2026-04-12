<?php

namespace App\Http\Resources\Admin\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientSimpleResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'email' => $this->email,
			'phone' => $this->phone,
			'ico' => $this->ico,
			'type' => $this->type,
			'city' => $this->city,
		];
	}
}
