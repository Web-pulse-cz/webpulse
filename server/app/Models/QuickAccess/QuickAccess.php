<?php

namespace App\Models\QuickAccess;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickAccess extends Model
{
    use HasFactory;

    protected $table = 'quick_access';

    protected $fillable = [
        'name',
        'link',
        'target',
        'position',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
