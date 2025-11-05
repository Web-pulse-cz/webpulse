<?php

namespace App\Listeners;

use App\Events\BiographySaved as Event;
use Illuminate\Support\Facades\App;

class BiographyGenerator
{

    public function handle(Event $event): void
    {
        $biography = $event->getBiography();

        if ($biography->filename && file_exists(storage_path('app/public/biographies/' . $biography->filename))) {
            unlink(storage_path('app/public/biographies/' . $biography->filename));
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
        if (!file_exists(storage_path('app/public/biographies/'))) {
            mkdir(storage_path('app/public/biographies/'), 0755, true);
        }
        $path = storage_path('app/public/biographies/' . $filename);
        file_put_contents($path, $output);
        $biography->filename = $filename;
        $biography->save();
    }
}
