<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait Fileable
{
    /**
     * Get all files attached to this model.
     */
    public function files(): Collection
    {
        return DB::table('fileables')
            ->where('fileable_id', $this->id)
            ->where('fileable_type', get_class($this))
            ->orderBy('position')
            ->get();
    }

    /**
     * Attach a file from an UploadedFile instance.
     */
    public function attachUploadedFile(UploadedFile $file, string $directory, ?string $name = null): object
    {
        $originalName = $file->getClientOriginalName();
        $fileName = $name ?? $originalName;
        $path = $file->store($directory, 'public');

        return $this->insertFile($fileName, $originalName, $path, $file->getMimeType(), $file->getSize());
    }

    /**
     * Attach a file from raw content (e.g. downloaded from API).
     */
    public function attachFileFromContent(string $content, string $path, string $name, ?string $mimeType = null): object
    {
        Storage::disk('public')->put($path, $content);
        $size = Storage::disk('public')->size($path);

        return $this->insertFile($name, $name, $path, $mimeType ?? 'application/octet-stream', $size);
    }

    /**
     * Remove a specific file by fileables ID.
     */
    public function removeFile(int $fileableId): bool
    {
        $file = DB::table('fileables')
            ->where('id', $fileableId)
            ->where('fileable_id', $this->id)
            ->where('fileable_type', get_class($this))
            ->first();

        if (! $file) {
            return false;
        }

        Storage::disk($file->disk)->delete($file->path);
        DB::table('fileables')->where('id', $fileableId)->delete();

        return true;
    }

    /**
     * Remove all files attached to this model.
     */
    public function removeAllFiles(): void
    {
        $files = $this->files();
        foreach ($files as $file) {
            Storage::disk($file->disk)->delete($file->path);
        }
        DB::table('fileables')
            ->where('fileable_id', $this->id)
            ->where('fileable_type', get_class($this))
            ->delete();
    }

    /**
     * Insert file record into database.
     */
    protected function insertFile(string $name, ?string $originalName, string $path, ?string $mimeType, int $size): object
    {
        $position = DB::table('fileables')
            ->where('fileable_id', $this->id)
            ->where('fileable_type', get_class($this))
            ->max('position') ?? -1;

        $id = DB::table('fileables')->insertGetId([
            'name' => $name,
            'original_name' => $originalName,
            'path' => $path,
            'disk' => 'public',
            'mime_type' => $mimeType,
            'size' => $size,
            'fileable_type' => get_class($this),
            'fileable_id' => $this->id,
            'position' => $position + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('fileables')->find($id);
    }
}
