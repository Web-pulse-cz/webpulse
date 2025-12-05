<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'tags',
        'status',
        'accuracy',
        'attempts',
        'user_id',
        'published_at'
    ];

    protected $casts = [
        'accuracy' => 'decimal:2',
        'attempts' => 'integer',
        'status' => 'string'
    ];

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id', 'id');
    }

    public function getUrlAttribute()
    {
        return sprintf('https://hry.martinhanzl.cz/kvizy/%s/%s', $this->id, $this->slug);
    }
}
