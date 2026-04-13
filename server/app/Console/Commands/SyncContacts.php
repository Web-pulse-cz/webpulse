<?php

namespace App\Console\Commands;

use App\Models\Contact\Contact;
use App\Models\Contact\ContactHistory;
use App\Models\Contact\ContactPhase;
use App\Models\Contact\ContactSource;
use Illuminate\Console\Command;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Revolution\Google\Sheets\Facades\Sheets;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

class SyncContacts extends Command
{
    use ResponseTrait;

    protected $signature = 'contacts:sync';

    protected $description = 'Sync contacts from XLXS file and import them to google spreadsheet.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $contacts = Contact::query()
            ->whereNotNull('phone')
            ->get();

        $this->output->progressStart(count($contacts));

        foreach ($contacts as $contact) {
            $newPhone = preg_replace('/(\d{3})(?=\d)/', '$1 ', preg_replace('/\s+/', '', $contact->phone));
            $contact->phone = $newPhone;
            $contact->save();
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        // $this->createContactHistories();
        // $this->databaseSync();
        // $this->writeToExcel();
    }

    private function createContactHistories(): void
    {
        $this->output->title('Creating contact histories...');

        ContactHistory::query()->delete();

        $contacts = Contact::query()->get();
        foreach ($contacts as $contact) {
            $history = new ContactHistory;
            $history->fill([
                'name' => 'Kontakt vytvořen',
                'description' => 'Vytvořili jste nový kontakt!',
                'origin' => 'system',
                'type' => 'other',
                'created_at' => $contact->created_at,
                'updated_at' => $contact->updated_at,
            ]);
            $history->contact()->associate($contact);
            $history->save();
        }
    }

    private function databaseSync(): void
    {
        $this->output->title('Syncing contacts...');

        Contact::query()->delete();

        $dataRaw = [];
        $rows = SimpleExcelReader::create(storage_path('app/Kontakty-0212024.xlsx'))->getRows();

        $rows->each(function (array $rowProperties) use (&$dataRaw) {
            array_push($dataRaw, $this->parseRow($rowProperties));
        });

        $data = [];
        foreach ($dataRaw as $key => $contact) {
            $data[] = [
                'firstname' => $contact['firstname'],
                'lastname' => $contact['lastname'],
                'phone' => $contact['phone'],
                'email' => null,
                'phase' => $contact['phase'],
                'company' => $contact['occupation'],
                'occupation' => $contact['occupation'],
                'goal' => $contact['goal'],
                'source' => $contact['source'],
                'next_meeting' => $contact['next_meeting'],
                'last_contacted_at' => $contact['last_contacted_at'],
                'note' => $contact['note'],
            ];
        }
        $data = json_decode(json_encode($data), false);
        $this->output->progressStart(count($data));
        foreach ($data as $key => $contact) {
            try {

                $phase = ContactPhase::query()->where('name', $contact->phase)
                    ->where('user_id', 1)
                    ->first();
                if (! $phase) {
                    $phase = new ContactPhase;
                    $phase->fill([
                        'name' => $contact->phase,
                        'color' => Str::upper(sprintf('#%s', Str::random(6))),
                    ]);
                    $phase->user_id = 1;
                    $phase->save();
                }

                $source = ContactSource::query()->where('name', $contact->source)
                    ->where('user_id', 1)
                    ->first();
                if (! $source) {
                    $source = new ContactSource;
                    $source->fill([
                        'name' => $contact->source,
                        'color' => Str::upper(sprintf('#%s', Str::random(6))),
                    ]);
                    $source->user_id = 1;
                    $source->save();
                }

                $checkContact = Contact::query()
                    ->where('firstname', $contact->firstname)
                    ->where('lastname', $contact->lastname)
                    ->where('phone', $contact->phone)
                    ->where('user_id', 1)
                    ->first();

                if (! $checkContact) {
                    $checkContact = new Contact;
                    $checkContact->fill([
                        'firstname' => $contact->firstname,
                        'lastname' => $contact->lastname,
                        'phone' => $contact->phone,
                        'email' => $contact->email,
                        'company' => $contact->company,
                        'occupation' => $contact->occupation,
                        'goal' => $contact->goal,
                        'note' => $contact->note,
                        'phase_id' => $phase->id,
                        'next_meeting' => $contact->next_meeting,
                        'last_contacted_at' => $contact->last_contacted_at,
                    ]);
                    $checkContact->user_id = 1;
                    $checkContact->contact_source_id = $source->id;
                    $checkContact->contact_phase_id = $phase->id;

                    $checkContact->save();
                }

                $this->output->progressAdvance();
            } catch (\Throwable|\Exception $e) {
                dd($e->getMessage());
            }
        }

        $this->output->progressFinish();
    }

    private function writeToExcel(): void
    {
        $this->output->title('Syncing contacts...');

        $rows = SimpleExcelReader::create(storage_path('app/Kontakty-3102023.xlsx'))->getRows();

        $writer = SimpleExcelWriter::create(storage_path('app/contacts.xlsx'));

        $rows->each(function (array $rowProperties) use ($writer) {
            $row = $this->parseRow($rowProperties);
            $writer->addRow($row);

            /*Sheets::spreadsheet('1wRQfN39iXeznrIfhZPjKC5an27hQSRgCvHBPj_5SHUk')
                ->sheet('test')->append([$row]);*/
        });
    }

    private function parseRow(array $row): array
    {
        $data = [
            'firstname' => count(explode(' ', $row['Jméno a příjmení'])) > 1 ? explode(' ', $row['Jméno a příjmení'])[1] : explode(' ', $row['Jméno a příjmení'])[0],
            'lastname' => explode(' ', $row['Jméno a příjmení'])[0],
            'phone' => ! in_array($row['Spojení'], ['', null, ' ', '-']) ? $row['Spojení'] : null,
            'phase' => ucfirst(strtolower($row['Fáze'])),
            'occupation' => ! in_array($row['Profese/studium'], ['', null, ' ', '-']) ? $row['Profese/studium'] : null,
            'goal' => ! in_array($row['Sen/cíl'], ['', null, ' ', '-']) ? $row['Sen/cíl'] : null,
            'source' => ucfirst(strtolower($row['Zdroj'])),
            'next_meeting' => ! in_array($row['Další schůzka'], ['', null, ' ', '-']) ? Carbon::parse($row['Další schůzka']) : null,
            'last_contacted_at' => ! in_array($row['Poslední kontakt'], ['', null, ' ', '-']) ? Carbon::parse($row['Poslední kontakt']) : null,
            'note' => ! in_array($row['Poznámky'], ['', null, ' ', '-']) ? $row['Poznámky'] : null,
        ];

        return $data;
    }
}
