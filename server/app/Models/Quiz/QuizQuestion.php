<?php

namespace App\Models\Quiz;

use App\Traits\Imagable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use Imagable;
    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id',
        'name',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'question_id', 'id');
    }

    public function getMainImageAttribute()
    {
        return $this->getMainImage($this);
    }

    public function getImagesAttribute()
    {
        return $this->imagesAttribute($this);
    }
}
