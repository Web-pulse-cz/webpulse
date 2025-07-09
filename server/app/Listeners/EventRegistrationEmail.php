<?php

namespace App\Listeners;

use App\Events\EventRegistrationSaved as Event;
use App\Models\Contact\ContactHistory;
use App\Services\EmailService;

class EventRegistrationEmail
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function handle(Event $event): void
    {
        $eventRegistration = $event->getEventRegistration();
        $eventRegistration->load('event');

        $this->emailService->buildEmail(
            'eventRegistration',
            'martas.hanzl@email.cz', // TODO: replace with dynamic email
            'Registrace na akci ' . $eventRegistration->event->name,
            data: ['eventRegistration' => $eventRegistration]
        );
    }
}
