<?php

namespace App\Console\Commands;

use App\Models\Contact\Contact;
use App\Models\Contact\ContactHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ContactsUpdateNextCall extends Command
{
    protected $signature = 'contacts:update-next-call';

    protected $description = 'Update next call dates for contacts.';

    public function handle()
    {
        $this->output->title('Updating next call dates');

        $contacts = Contact::query()
            ->whereNotNull('next_contact')
            ->orderBy('id', 'desc')
            ->get();

        $this->output->progressStart(count($contacts));
        foreach ($contacts as $contact) {
            $next_contact = Carbon::parse($contact->next_contact);
            if ($next_contact->isPast()) {
                $contact->next_contact = Carbon::now()->endOfDay();
                $contact->save();

                $history = new ContactHistory();
                $history->fill([
                    'name' => 'Systémová změna',
                    'description' => 'Tomuto kontaktu jste se dlouho neozvali. Systém proto změnil datum dalšího telefonátu na dnes.',
                    'origin' => 'system',
                    'type' => 'other',
                    'contact_id' => $contact->id,
                    'contact_phase_id' => $contact->contact_phase_id,
                    'activity_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $history->save();
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }
}
