<?php

namespace App\Models\PersonalGrowth;

use App\Traits\Imagable;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasPersonalGrowthTasks, Imagable;

    protected $table = 'books';

    protected $fillable = [
        'name',
        'author',
        'notes',
        'keywords',
        'is_read',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_read' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(fn (Book $book) => $book->saveImages($book, null));
    }

    public function getMainImageAttribute(): ?string
    {
        return $this->getMainImage($this);
    }
}
