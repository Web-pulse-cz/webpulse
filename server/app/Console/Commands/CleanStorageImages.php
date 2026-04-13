<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanStorageImages extends Command
{
    protected $signature = 'storage:clean-images';

    protected $description = 'Remove and delete unused images from storage';

    public function handle(): void
    {
        $images = $this->getStorageImages();

        $this->output->progressStart(count($images));

        foreach ($images as $image) {
            $exists = DB::table('images')->where('image', $filename)->exists();
            if ($exists) {
                $this->deleteImage($image['path']);
            }
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    private function getStorageImages(): array
    {
        $images = [];
        $disk = Storage::disk('public');
        $files = $disk->allFiles('images');

        foreach ($files as $file) {
            $filename = explode('/', $file);
            $filename = end($filename);
            $images[] = [
                'path' => $file,
                'filename' => $filename,
            ];
        }

        return $images;
    }

    private function deleteImage(string $path): void
    {
        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        } else {
            $this->warn("Image not found: {$path}");
        }
    }
}
