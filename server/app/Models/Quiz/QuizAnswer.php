<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $table = 'quiz_answers';

    protected $fillable = [
        'question_id',
        'name',
        'is_correct'
    ];

    protected $casts = [
        'is_correct' => 'boolean'
    ];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id', 'id');
    }
}
