<?php

namespace App\Listeners;

use App\Events\QuizSaved as Event;
use App\Models\Newsletter\Newsletter;
use App\Services\EmailService;

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

        if ($quiz->status == 'public' && $quiz->published_at == null) {
            $newsletters = Newsletter::query()->get();
            foreach ($newsletters as $newsletter) {
                $this->emailService->buildEmail(
                    'quizSaved',
                    $newsletter->email,
                    'Nový kvíz vytvořený: '.$quiz->name,
                    null,
                    null,
                    [
                        'quiz' => $quiz,
                        'newsletter' => $newsletter,
                    ]
                );
            }
            $quiz->published_at = now();
            $quiz->save();
        }
    }
}
