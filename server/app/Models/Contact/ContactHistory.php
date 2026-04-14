<?php

namespace App\Models\Contact;

use App\Models\Activity\Activity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactHistory extends Model
{
    use HasFactory;

    protected $table = 'contact_histories';

    protected $fillable = [
        'name',
        'description',
        'origin',
        'type',
        'contact_id',
        'contact_phase_id',
        'activity_id',
    ];

    protected $with = ['phase', 'activity'];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function phase()
    {
        return $this->belongsTo(ContactPhase::class, 'contact_phase_id', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }
}
