<?php

namespace App\Console\Commands;

use App\Models\Blog\Post;
use App\Models\Career\Career;
use App\Models\Event\Event;
use App\Models\Logo\Logo;
use App\Models\Novelty\Novelty;
use App\Models\Project\Project;
use App\Models\Quiz\QuizQuestion;
use App\Models\Service\Service;
use Illuminate\Console\Command;
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
            if (!$this->imageExistsInDatabase($image['filename'])) {
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

    private function imageExistsInDatabase(string $filename): bool
    {
        $models = [
            Career::class,
            Event::class,
            Logo::class,
            Novelty::class,
            Post::class,
            Project::class,
            QuizQuestion::class,
            Service::class,
        ];

        foreach ($models as $model) {
            if ($model::query()->where('image', $filename)->exists()) {
                return true;
            }
        }

        return false;
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
