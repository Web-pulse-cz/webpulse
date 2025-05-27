<?php

namespace App\Http\Resources\Admin\User;

use App\Http\Resources\Admin\QickAccess\QuickAccessResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'avatar' => $this->avatar,
            'email' => $this->email,
            'phone_prefix' => $this->phone_prefix,
            'phone' => $this->phone,
            'street' => $this->street,
            'city' => $this->city,
            'zip' => $this->zip,
            'invitation_token' => $this->invitation_token,
            'quick_access' => QuickAccessResource::collection($this->quickAccess),
            'user_group_id' => $this->user_group_id,
            'user_group' => UserGroupResource::make($this->userGroup),
        ];
    }
}
