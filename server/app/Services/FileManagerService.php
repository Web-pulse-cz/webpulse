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

    private const SVG_EXTENSIONS = ['svg', 'svgz'];

    private ?ImageManager $imageManager = null;

    public function __construct()
    {
        // Image manager (and its underlying Imagick driver) is created lazily
        // to keep this service safely instantiable in environments without
        // the Imagick PHP extension (e.g. CLI introspection, route:list).
    }

    private function imageManager(): ImageManager
    {
        if ($this->imageManager === null) {
            $this->imageManager = new ImageManager(new Driver);
        }

        return $this->imageManager;
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

        // Bail out before touching the disk if there's nothing configured for
        // this entity_type — otherwise we'd silently return filenames that point
        // to files that were never actually written.
        if (empty($imageFormats)) {
            throw new \Exception(
                $format
                    ? "Pro typ '{$type}' / formát '{$format}' není nastavený žádný preset. Vytvořte ho v Nastavení → Filemanager."
                    : "Pro typ '{$type}' není nastavený žádný formát. Vytvořte preset v Nastavení → Filemanager."
            );
        }

        if (! empty($files)) {
            // Validate everything up front so a mid-batch failure doesn't leave
            // orphan files on disk for whatever was processed before the error.
            foreach ($files as $i => $file) {
                if (! $file->isValid()) {
                    throw new \Exception("Soubor #".($i + 1)." je neplatný (".$file->getErrorMessage().").");
                }
            }

            foreach ($files as $file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $filename = $keepName ? $file->getClientOriginalName() : uniqid('', true).'.'.$extension;
                $isSvg = in_array($extension, self::SVG_EXTENSIONS, true) || $file->getMimeType() === 'image/svg+xml';

                foreach ($imageFormats as $configFormat) {
                    $this->ensureDirectoryExists($configFormat['path']);

                    if ($isSvg) {
                        copy($file->getRealPath(), $configFormat['path'].'/'.$filename);

                        continue;
                    }

                    try {
                        $imageData = $this->parseImage($file, $configFormat, $extension);
                    } catch (\Exception $e) {
                        throw new \Exception('Error processing image: '.$e->getMessage());
                    }

                    $this->saveImage($imageData, $configFormat['path'].'/'.$filename, $extension);
                }

                $uploadedImages[] = $filename;
            }
        } elseif ($url) {
            $imageContents = file_get_contents($url);
            if ($imageContents === false) {
                throw new \Exception('Failed to fetch image from URL.');
            }

            $urlExtension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            if (! $urlExtension) {
                $urlExtension = 'jpg';
            }

            $tempFilePath = tempnam(sys_get_temp_dir(), 'img_').'.'.$urlExtension;
            file_put_contents($tempFilePath, $imageContents);

            $file = new File($tempFilePath);
            $filename = $keepName ? basename(parse_url($url, PHP_URL_PATH)) : uniqid('', true).'.'.$urlExtension;
            $isSvg = in_array($urlExtension, self::SVG_EXTENSIONS, true);

            foreach ($imageFormats as $configFormat) {
                $this->ensureDirectoryExists($configFormat['path']);

                if ($isSvg) {
                    copy($tempFilePath, $configFormat['path'].'/'.$filename);

                    continue;
                }

                try {
                    $imageData = $this->parseImage($file, $configFormat, $urlExtension);
                } catch (\Exception $e) {
                    throw new \Exception('Error processing image from URL: '.$e->getMessage());
                }

                $this->saveImage($imageData, $configFormat['path'].'/'.$filename, $urlExtension);
            }

            $uploadedImages[] = $filename;

            unlink($tempFilePath);
        }

        return $uploadedImages;
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
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

    private function parseImage($file, array $format, string $extension): ImageInterface
    {
        $image = $this->imageManager()->read($file);

        $width = $format['width'] ?? null;
        $height = $format['height'] ?? null;
        $mode = $format['mode'] ?? 'cover';
        $position = $format['crop_position'] ?? 'center';

        if (! in_array($position, self::ALLOWED_POSITIONS, true)) {
            $position = 'center';
        }

        if ($width && $height) {
            // PNG/WEBP/GIF support transparency — pad with transparent background
            // so we never burn a white background into a transparent image.
            $padBackground = $this->supportsTransparency($extension) ? 'transparent' : 'ffffff';

            match ($mode) {
                'contain' => $image->pad($width, $height, $padBackground, $position),
                'stretch' => $image->resize($width, $height),
                default => $image->cover($width, $height, $position),
            };
        }

        return $image;
    }

    private function saveImage(ImageInterface $image, string $path, string $extension): void
    {
        // Encode by the requested extension so the output format always matches
        // the input (PNG stays PNG, JPG stays JPG, WEBP stays WEBP, …).
        $encoded = $image->encodeByExtension($extension);
        $encoded->save($path);
    }

    private function supportsTransparency(string $extension): bool
    {
        return in_array($extension, ['png', 'webp', 'gif', 'avif'], true);
    }
}
