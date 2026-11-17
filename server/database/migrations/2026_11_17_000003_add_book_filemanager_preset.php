<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $entityType = 'book';

    // Obálky knih (poměr 2:3) — preset pro všechny weby, upravitelný v Nastavení → Filemanager.
    private array $formats = [
        ['format' => 'medium', 'width' => 400, 'height' => 600, 'path' => 'medium', 'position' => 0],
        ['format' => 'large', 'width' => 800, 'height' => 1200, 'path' => 'large', 'position' => 1],
    ];

    public function up(): void
    {
        $siteIds = DB::table('sites')->pluck('id');
        $now = now();

        foreach ($this->formats as $format) {
            $exists = DB::table('filemanagers')
                ->where('entity_type', $this->entityType)
                ->where('format', $format['format'])
                ->exists();
            if ($exists) {
                continue;
            }

            $id = DB::table('filemanagers')->insertGetId([
                'entity_type' => $this->entityType,
                'format' => $format['format'],
                'width' => $format['width'],
                'height' => $format['height'],
                'mode' => 'cover',
                'crop_position' => 'center',
                'path' => $format['path'],
                'position' => $format['position'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('filemanagers_has_sites')->insert(
                $siteIds->map(fn ($siteId) => [
                    'filemanager_id' => $id,
                    'site_id' => $siteId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        }
    }

    public function down(): void
    {
        DB::table('filemanagers')->where('entity_type', $this->entityType)->delete();
    }
};
