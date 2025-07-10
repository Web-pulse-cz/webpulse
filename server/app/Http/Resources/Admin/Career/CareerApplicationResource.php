<?php

namespace App\Http\Resources\Admin\Career;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerApplicationResource extends JsonResource
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
            'career_id' => $this->career_id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'cover_letter' => $this->cover_letter,
            'resume' => $this->resume,
            'status' => $this->status,
            'salary_expectation' => $this->salary_expectation,
            'availability' => $this->realAvailability,
            'source' => $this->source,
            'locale' => $this->locale,
            'career' => [
                'id' => $this->career->id,
                'name' => $this->career->name,
                'code' => $this->career->code,
            ],
            'applied_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->user ? $this->user->id : null,
                'name' => $this->user ? `${$this->user->firstname} ${$this->user->lastname}` : null,
                'email' => $this->user ? $this->user->email : null,
            ],
        ];
    }
}
