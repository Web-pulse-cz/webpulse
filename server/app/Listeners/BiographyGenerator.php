<?php

namespace App\Listeners;

use App\Events\BiographySaved as Event;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class BiographyGenerator
{
    const PATH = 'app/public/files/biographies/';
    public function handle(Event $event): void
    {
        $biography = $event->getBiography();

        if ($biography->filename && File::exists(storage_path(self::PATH . $biography->filename))) {
            unlink(storage_path(self::PATH . $biography->filename));
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('pdf.biography.' . $biography->template,
            [
                'biography' => $biography,
                'user' => $event->getUser(),
            ]
        );
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['dpi' => 300, 'defaultFont' => 'DejaVu Sans']);
        $output = $pdf->output();

        $filename = 'biography_' . $biography->id . '_' . time() . '.pdf';
        if (!file_exists(storage_path(self::PATH))) {
            mkdir(storage_path(self::PATH), 0755, true);
        }
        $path = storage_path(self::PATH . $filename);
        file_put_contents($path, $output);

        $biography->filename = $filename;
        $biography->save();
    }
}
