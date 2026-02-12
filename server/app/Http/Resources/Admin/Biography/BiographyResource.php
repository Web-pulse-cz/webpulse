<?php

namespace App\Http\Resources\Admin\Biography;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BiographyResource extends JsonResource
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
            'template' => $this->template,
            'phone_prefix' => $this->phone_prefix,
            'phone' => $this->phone,
            'email' => $this->email,
            'linkedin' => $this->linkedin,
            'github' => $this->github,
            'website' => $this->website,
            'address' => $this->address,
            'about_me' => $this->about_me,
            'job_experiences' => $this->job_experiences,
            'education' => $this->education,
            'skills' => $this->skills,
            'hard_skills' => $this->hard_skills,
            'soft_skills' => $this->soft_skills,
            'filename' => $this->filename,
            'job_title' => $this->job_title,
            'summary' => $this->summary,
        ];
    }
}
