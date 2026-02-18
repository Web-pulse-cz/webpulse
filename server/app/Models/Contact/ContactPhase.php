<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactPhase extends Model
{
    use HasFactory;

    protected $table = 'contact_phases';

    protected $fillable = [
        'name',
        'color',
        'user_id',
        'position',
        'show_in_statistics'
    ];

    protected $casts = [
        'show_in_statistics' => 'boolean'
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'contact_phase_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(ContactHistory::class, 'contact_phase_id', 'id');
    }

    public function tasks()
    {
        return $this->hasMany(ContactTask::class, 'contact_phase_id', 'id');
    }
}
