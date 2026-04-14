<?php

namespace App\Events;

use App\Models\Contact\Contact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Contact $oldContact;

    protected Contact $newContact;

    public function __construct(Contact $oldContact, Contact $newContact)
    {
        $this->oldContact = $oldContact;
        $this->newContact = $newContact;
    }

    public function getOldContact(): Contact
    {
        return $this->oldContact;
    }

    public function getNewContact(): Contact
    {
        return $this->newContact;
    }
}
