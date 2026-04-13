<?php

namespace App\Console\Commands;

use App\Models\Contact\ContactPhase;
use App\Models\Contact\ContactSource;
use Illuminate\Console\Command;

class Registration extends Command
{
    protected $colors = [
        'indigo', 'red', 'green', 'yellow', 'blue', 'purple', 'pink', 'orange', 'teal', 'cyan', 'gray', 'black',
    ];

    protected $signature = 'app:registration {--userId=}';

    protected $description = 'Create default values for the app for every new user registration';

    public function handle()
    {
        $userId = $this->option('userId');
        if (! $userId) {
            $this->error('User id is required');

            return;
        } else {
            $this->seedContacts($userId);
        }
    }

    private function seedContacts(int $userId): void
    {
        $contactSources = [
            ['name' => 'Základní škola', 'color' => $this->getRandomColor()],
            ['name' => 'Střední škola', 'color' => $this->getRandomColor()],
            ['name' => 'Práce', 'color' => $this->getRandomColor()],
            ['name' => 'Sportovní kroužek', 'color' => $this->getRandomColor()],
        ];

        $contactPhases = [
            ['name' => 'Nekontaktován/a', 'color' => $this->getRandomColor()],
            ['name' => 'Kontaktován/a', 'color' => $this->getRandomColor()],
            ['name' => 'Po KM1', 'color' => $this->getRandomColor()],
            ['name' => 'Po navazující schůzce', 'color' => $this->getRandomColor()],
            ['name' => 'Po KM2', 'color' => $this->getRandomColor()],
            ['name' => 'Po follow-upu', 'color' => $this->getRandomColor()],
            ['name' => 'Registrován/a', 'color' => $this->getRandomColor()],
        ];

        foreach ($contactSources as $contactSource) {
            $contactSource['user_id'] = $userId;
            $newContactSource = new ContactSource;
            $newContactSource->fill($contactSource);
            $newContactSource->save();
        }

        foreach ($contactPhases as $contactPhase) {
            $contactPhase['user_id'] = $userId;
            $newContactPhase = new ContactPhase;
            $newContactPhase->fill($contactPhase);
            $newContactPhase->save();
        }
    }

    private function getRandomColor(): string
    {
        return $this->colors[array_rand($this->colors)];
    }
}
