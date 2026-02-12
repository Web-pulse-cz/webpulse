<?php

namespace App\Models\Message;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageBlueprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'message',
        'type',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
