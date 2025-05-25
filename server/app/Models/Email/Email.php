<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $table = 'emails';

    protected $fillable = [
        'from',
        'to',
        'subject',
        'cc',
        'bcc',
        'html',
        'plain',
        'attachments',
        'status',
        'attempts',
        'sent_at',
        'locale',
        'template',
        'priority',
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'attachments' => 'array',
        'sent_at' => 'datetime',
    ];
}
