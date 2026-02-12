<?php

namespace App\Listeners;

use App\Events\DemandSaved as Event;
use App\Models\Contact\ContactHistory;
use App\Services\EmailService;

class DemandEmail
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function handle(Event $event): void
    {
        $demand = $event->getDemand();

        $this->emailService->buildEmail(
            'demand',
            'martas.hanzl@email.cz',
            'Nová poptávka',
            data: ['demand' => $demand],
            locale: $demand->locale,
        );
    }
}
