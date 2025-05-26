<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class FileManagerService
{
    private const IMAGES_BASE_PATH = 'images';
    private const FILES_BASE_PATH = 'files';

    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function getImageFormats(string $type, string $format = null): array
    {
        $config = config(sprintf('filemanager.images.%s', $type), []);
        $formats = [];

        foreach ($config as $item) {
            if ($format && $item['format'] === $format) {
                return [
                    'format' => $item['format'],
                    'width' => $item['width'],
                    'height' => $item['height'],
                    'keepAspectRatio' => $item['keepAspectRatio'],
                    'path' => storage_path('app/public/' . self::IMAGES_BASE_PATH . '/' . $type . '/' . $item['path']),
                ];
            }
            $formats[] = [
                'format' => $item['format'],
                'width' => $item['width'],
                'height' => $item['height'],
                'keepAspectRatio' => $item['keepAspectRatio'],
                'path' => storage_path('app/public/' . self::IMAGES_BASE_PATH . '/' . $type . '/' . $item['path']),
            ];
        }

        return $formats;
    }

    public function uploadImages(array $files, string $type, string $format = null, int $keepName = 0): array
    {
        $uploadedImages = [];

        // Validate type
        $imageFormats = $this->getImageFormats($type, $format);
        foreach ($files as $file) {
            // Validate file
            if (!$file->isValid()) {
                throw new \Exception("Invalid file upload.");
            }

            $filename = $keepName ? $file->getClientOriginalName() : uniqid('', true) . '.' . strtolower($file->getClientOriginalExtension());
            foreach ($imageFormats as $configFormat) {
                try {
                    $imageData = $this->parseImage($file, $configFormat);
                } catch (\Exception $e) {
                    throw new \Exception("Error processing image: " . $e->getMessage());
                }

                // Ensure the directory exists
                if (!is_dir($configFormat['path'])) {
                    mkdir($configFormat['path'], 0755, true);
                }


                // Save the processed image
                $imageData->save($configFormat['path'] . '/' . $filename);

            }
            $uploadedImages[] = $filename;
        }

        return $uploadedImages;
    }

    private function parseImage($file, array $format): \Intervention\Image\Interfaces\ImageInterface
    {
        $image = $this->imageManager->read($file);

        $width = $format['width'] ?? null;
        $height = $format['height'] ?? null;
        $keepAspectRatio = $format['keepAspectRatio'] ?? true;

        if ($width && $height) {
            if ($keepAspectRatio) {
                $image->pad($width, $height);
            } else {
                $image->resize($width, $height);
            }
        }

        return $image;
    }
}
