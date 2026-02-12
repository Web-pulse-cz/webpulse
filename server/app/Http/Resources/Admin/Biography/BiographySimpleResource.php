<?php

namespace App\Http\Resources\Admin\Biography;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BiographySimpleResource extends JsonResource
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
            'name' => $this->name,
            'job_title' => $this->job_title,
            'template' => $this->template,
            'filename' => $this->filename,
            'updated_at' => $this->updated_at,
        ];
    }
}
