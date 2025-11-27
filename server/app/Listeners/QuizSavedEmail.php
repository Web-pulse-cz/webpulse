<?php

namespace App\Listeners;

use App\Events\QuizSaved as Event;
use App\Models\Newsletter\Newsletter;
use App\Services\EmailService;
use Illuminate\Support\Facades\App;

class QuizSavedEmail
{

    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function handle(Event $event): void
    {
        $quiz = $event->getQuiz();

        if ($quiz->published_at != null) {
            return;
        }

        $newsletters = Newsletter::query()->get();
        foreach ($newsletters as $newsletter) {
            $this->emailService->buildEmail(
                'quizSaved',
                $newsletter->email,
                'Nový kvíz vytvořený: ' . $quiz->name,
                null,
                null,
                [
                    'quiz' => $quiz,
                    'newsletter' => $newsletter,
                ]
            );
        }
    }
}
