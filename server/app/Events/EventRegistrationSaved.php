<?php

namespace App\Events;

use App\Models\Event\EventRegistration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventRegistrationSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected EventRegistration $eventRegistration;

    protected array $data;
    public function __construct(EventRegistration $eventRegistration)
    {
        $this->eventRegistration = $eventRegistration;
    }

    public function getEventRegistration(): EventRegistration
    {
        return $this->eventRegistration;
    }
}
