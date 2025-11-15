<?php

namespace App\Events;

use App\Models\Quiz\Quiz;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Quiz $quiz;
    public function __construct(Quiz $quiz)
    {
        $this->quiz = $quiz;
    }

    public function getQuiz(): Quiz
    {
        return $this->quiz;
    }
}
