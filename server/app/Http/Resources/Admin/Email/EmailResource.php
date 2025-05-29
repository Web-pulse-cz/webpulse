<?php

namespace App\Http\Resources\Admin\Email;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailResource extends JsonResource
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
            'to' => $this->to,
            'subject' => $this->subject,
            'cc' => implode(', ', $this->cc),
            'bcc' => implode(', ', $this->bcc),
            'html' => $this->html,
            'attachments' => $this->attachments,
            'priority' => $this->priority,
            'status' => $this->status,
            'attempts' => $this->attempts,
            'sent_at' => $this->sent_at->format('Y-m-d H:i:s'),
            'locale' => $this->locale,
            'template' => $this->template,
            'sent' => $this->status === 'sent',
        ];
    }
}
