<?php

namespace App\Services;

use App\Models\Filemanager\Filemanager;
use App\Models\Site\Site;
use Illuminate\Http\File;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class FileManagerService
{
    private const IMAGES_BASE_PATH = 'images';

    private const FILES_BASE_PATH = 'files';

    private const ALLOWED_POSITIONS = [
        'top-left', 'top', 'top-right',
        'left', 'center', 'right',
        'bottom-left', 'bottom', 'bottom-right',
    ];

    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver);
    }

    public function getImageFormats(string $type, ?string $format = null): array
    {
        $siteId = $this->resolveSiteIdFromRequest();

        $query = Filemanager::query()
            ->where('entity_type', $type)
            ->orderBy('position');

        if ($siteId) {
            $query->whereRelation('sites', 'sites.id', $siteId);
        }

        if ($format) {
            $row = $query->where('format', $format)->first();

            return $row ? $this->serializeRow($row, $type) : [];
        }

        return $query->get()
            ->map(fn ($row) => $this->serializeRow($row, $type))
            ->toArray();
    }

    public function uploadImages(string $type, ?string $format = null, int $keepName = 0, array $files = [], ?string $url = null): array
    {
        $uploadedImages = [];

        $imageFormats = $this->getImageFormats($type, $format);
        if ($format && ! empty($imageFormats) && isset($imageFormats['format'])) {
            // Single-format response — wrap to a list to keep the loop uniform
            $imageFormats = [$imageFormats];
        }

        if (! empty($files)) {
            foreach ($files as $file) {
                if (! $file->isValid()) {
                    throw new \Exception('Invalid file upload.');
                }

                $extension = strtolower($file->getClientOriginalExtension());
                $filename = $keepName ? $file->getClientOriginalName() : uniqid('', true).'.'.$extension;

                foreach ($imageFormats as $configFormat) {
                    if (in_array($extension, ['svg', 'svgz']) || $file->getMimeType() === 'image/svg+xml') {
                        if (! is_dir($configFormat['path'])) {
                            mkdir($configFormat['path'], 0755, true);
                        }
                        copy($file->getRealPath(), $configFormat['path'].'/'.$filename);

                        continue;
                    }

                    try {
                        $imageData = $this->parseImage($file, $configFormat);
                    } catch (\Exception $e) {
                        throw new \Exception('Error processing image: '.$e->getMessage());
                    }

                    if (! is_dir($configFormat['path'])) {
                        mkdir($configFormat['path'], 0755, true);
                    }

                    $imageData->save($configFormat['path'].'/'.$filename);
                }

                $uploadedImages[] = $filename;
            }
        } elseif ($url) {
            $imageContents = file_get_contents($url);
            if ($imageContents === false) {
                throw new \Exception('Failed to fetch image from URL.');
            }

            $tempFilePath = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tempFilePath, $imageContents);

            $file = new File($tempFilePath);
            $filename = $keepName ? basename(parse_url($url, PHP_URL_PATH)) : uniqid('', true).'.jpg';

            foreach ($imageFormats as $configFormat) {
                try {
                    $imageData = $this->parseImage($file, $configFormat);
                } catch (\Exception $e) {
                    throw new \Exception('Error processing image from URL: '.$e->getMessage());
                }

                if (! is_dir($configFormat['path'])) {
                    mkdir($configFormat['path'], 0755, true);
                }

                $imageData->save($configFormat['path'].'/'.$filename);
            }

            $uploadedImages[] = $filename;

            unlink($tempFilePath);
        }

        return $uploadedImages;
    }

    private function serializeRow(Filemanager $row, string $type): array
    {
        return [
            'format' => $row->format,
            'width' => $row->width,
            'height' => $row->height,
            'mode' => $row->mode,
            'crop_position' => $row->crop_position,
            'path' => storage_path('app/public/'.self::IMAGES_BASE_PATH.'/'.$type.'/'.$row->path),
        ];
    }

    private function resolveSiteIdFromRequest(): ?int
    {
        $hash = request()->header('X-Site-Hash');
        if (! $hash) {
            return null;
        }

        $site = Site::query()->where('hash', $hash)->first();

        return $site?->id;
    }

    private function parseImage($file, array $format): ImageInterface
    {
        $image = $this->imageManager->read($file);

        $width = $format['width'] ?? null;
        $height = $format['height'] ?? null;
        $mode = $format['mode'] ?? 'cover';
        $position = $format['crop_position'] ?? 'center';

        if (! in_array($position, self::ALLOWED_POSITIONS, true)) {
            $position = 'center';
        }

        if ($width && $height) {
            match ($mode) {
                'contain' => $image->pad($width, $height),
                'stretch' => $image->resize($width, $height),
                default => $image->cover($width, $height, $position),
            };
        }

        return $image;
    }
}
