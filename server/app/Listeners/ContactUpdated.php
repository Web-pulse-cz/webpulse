<?php

namespace App\Listeners;

use App\Events\ContactUpdatedEvent as Event;
use App\Models\Contact\ContactHistory;

class ContactUpdated
{
    public function handle(Event $event): void
    {
        $oldContact = $event->getOldContact();
        $newContact = $event->getNewContact();

        if (empty($oldContact->goal) && ! empty($newContact->goal)) {
            $history = new ContactHistory;
            $history->fill([
                'name' => 'Už znáš jeho/její cíl!',
                'description' => $newContact->goal,
                'origin' => 'system',
                'type' => 'call',
            ]);
            $history->contact()->associate($newContact);
            $history->save();
        }

        if ($oldContact->contact_phase_id !== $newContact->contact_phase_id) {
            $history = new ContactHistory;
            $history->fill([
                'name' => 'Fáze kontaktu změněna',
                'description' => 'Fáze kontaktu byla změněna z '.$oldContact->phase?->name.' na '.$newContact->phase->name,
                'origin' => 'system',
                'type' => 'call',
            ]);
            $history->contact()->associate($newContact);
            $history->save();
        }
    }
}
