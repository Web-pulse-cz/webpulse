<?php

namespace App\Http\Resources\Admin\Filemanager;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilemanagerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type,
            'format' => $this->format,
            'width' => $this->width,
            'height' => $this->height,
            'mode' => $this->mode,
            'crop_position' => $this->crop_position,
            'path' => $this->path,
            'position' => $this->position,
            'sites' => $this->whenLoaded('sites', fn () => $this->sites->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'hash' => $s->hash,
            ])),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
