<?php

namespace App\Http\Resources\Admin\User;

use App\Http\Resources\Admin\QickAccess\QuickAccessResource;
use App\Http\Resources\Admin\Site\SiteResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginResource extends JsonResource
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
            'avatar' => $this->avatar,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'quick_access' => QuickAccessResource::collection($this->quickAccess),
            'user_group_id' => $this->user_group_id,
            'user_group' => UserGroupResource::make($this->userGroup),
            'sites' => SiteResource::collection($this->sites),
        ];
    }
}
