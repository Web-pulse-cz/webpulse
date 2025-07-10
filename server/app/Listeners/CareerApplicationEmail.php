<?php

namespace App\Listeners;

use App\Events\CareerApplicationSaved as Event;

use App\Services\EmailService;

class CareerApplicationEmail
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function handle(Event $event): void
    {
        $careerApplication = $event->getCareerApplication();
        $careerApplication->load('career');

        // build and add email to queue for client
        $this->emailService->buildEmail(
            'careerApplication',
            'martas.hanzl@email.cz', // TODO: replace with dynamic email
            'Žádost o pracovní pozici ' . $careerApplication->career->name,
            data: ['careerApplication' => $careerApplication, 'type' => 'client']
        );

        // build and add email to queue for employee
        $this->emailService->buildEmail(
            'careerApplication',
            'martas.hanzl@email.cz', // TODO: replace with dynamic email
            'Nová žádost o pracovní pozici ' . $careerApplication->career->name,
            data: ['careerApplication' => $careerApplication, 'type' => 'admin']
        );
    }
}
