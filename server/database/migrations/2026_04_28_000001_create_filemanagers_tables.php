<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filemanagers', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 64)->index();
            $table->string('format', 64);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('mode', 16)->default('cover');
            $table->string('crop_position', 16)->default('center');
            $table->string('path', 128);
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('filemanagers_has_sites', function (Blueprint $table) {
            $table->unsignedBigInteger('filemanager_id');
            $table->foreign('filemanager_id')->references('id')->on('filemanagers')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['filemanager_id', 'site_id']);
            $table->timestamps();
        });

        $this->seedFromLegacyConfig();
    }

    public function down(): void
    {
        Schema::dropIfExists('filemanagers_has_sites');
        Schema::dropIfExists('filemanagers');
    }

    private function seedFromLegacyConfig(): void
    {
        // Snapshot of config/filemanager.php at the time of this migration.
        // keepAspectRatio:true  -> mode=cover,   crop_position=center (no white-bg pad)
        // keepAspectRatio:false -> mode=stretch, crop_position=center (preserve existing distortion)
        $legacy = [
            'service' => [
                ['format' => 'small', 'width' => 200, 'height' => 200, 'keepAspectRatio' => true, 'path' => 'small'],
                ['format' => 'medium', 'width' => 400, 'height' => 400, 'keepAspectRatio' => true, 'path' => 'medium'],
                ['format' => 'large', 'width' => 800, 'height' => 800, 'keepAspectRatio' => true, 'path' => 'large'],
            ],
            'user' => [
                ['format' => 'icon', 'width' => 32, 'height' => 32, 'keepAspectRatio' => true, 'path' => 'icon'],
                ['format' => 'thumb', 'width' => 190, 'height' => 190, 'keepAspectRatio' => true, 'path' => 'thumb'],
            ],
            'novelty' => [
                ['format' => 'small', 'width' => 200, 'height' => 200, 'keepAspectRatio' => true, 'path' => 'small'],
                ['format' => 'medium', 'width' => 400, 'height' => 400, 'keepAspectRatio' => true, 'path' => 'medium'],
                ['format' => 'large', 'width' => 800, 'height' => 800, 'keepAspectRatio' => true, 'path' => 'large'],
            ],
            'post' => [
                ['format' => 'medium', 'width' => 450, 'height' => 310, 'keepAspectRatio' => false, 'path' => 'medium'],
                ['format' => 'large', 'width' => 700, 'height' => 400, 'keepAspectRatio' => false, 'path' => 'large'],
            ],
            'post_category' => [
                ['format' => 'small', 'width' => 200, 'height' => 200, 'keepAspectRatio' => true, 'path' => 'small'],
                ['format' => 'medium', 'width' => 400, 'height' => 400, 'keepAspectRatio' => true, 'path' => 'medium'],
                ['format' => 'large', 'width' => 800, 'height' => 800, 'keepAspectRatio' => true, 'path' => 'large'],
            ],
            'logo' => [
                ['format' => 'small', 'width' => 64, 'height' => 64, 'keepAspectRatio' => true, 'path' => 'small'],
                ['format' => 'medium', 'width' => 128, 'height' => 128, 'keepAspectRatio' => true, 'path' => 'medium'],
                ['format' => 'large', 'width' => 256, 'height' => 256, 'keepAspectRatio' => true, 'path' => 'large'],
            ],
            'event' => [
                ['format' => 'small', 'width' => 64, 'height' => 64, 'keepAspectRatio' => true, 'path' => 'small'],
                ['format' => 'medium', 'width' => 128, 'height' => 128, 'keepAspectRatio' => true, 'path' => 'medium'],
                ['format' => 'large', 'width' => 256, 'height' => 256, 'keepAspectRatio' => true, 'path' => 'large'],
            ],
            'career' => [
                ['format' => 'small', 'width' => 64, 'height' => 64, 'keepAspectRatio' => true, 'path' => 'small'],
                ['format' => 'medium', 'width' => 128, 'height' => 128, 'keepAspectRatio' => true, 'path' => 'medium'],
                ['format' => 'large', 'width' => 256, 'height' => 256, 'keepAspectRatio' => true, 'path' => 'large'],
            ],
            'quiz' => [
                ['format' => 'small', 'width' => 64, 'height' => 64, 'keepAspectRatio' => true, 'path' => 'small'],
                ['format' => 'medium', 'width' => 128, 'height' => 128, 'keepAspectRatio' => true, 'path' => 'medium'],
                ['format' => 'large', 'width' => 256, 'height' => 256, 'keepAspectRatio' => true, 'path' => 'large'],
                ['format' => 'screen', 'width' => 1024, 'height' => 768, 'keepAspectRatio' => true, 'path' => 'screen'],
            ],
        ];

        $now = now();
        $rows = [];
        foreach ($legacy as $entityType => $presets) {
            foreach ($presets as $position => $preset) {
                $rows[] = [
                    'entity_type' => $entityType,
                    'format' => $preset['format'],
                    'width' => $preset['width'],
                    'height' => $preset['height'],
                    'mode' => $preset['keepAspectRatio'] ? 'cover' : 'stretch',
                    'crop_position' => 'center',
                    'path' => $preset['path'],
                    'position' => $position,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('filemanagers')->insert($rows);

        $siteId = 1;
        $insertedIds = DB::table('filemanagers')->pluck('id');
        $pivot = $insertedIds->map(fn ($id) => [
            'filemanager_id' => $id,
            'site_id' => $siteId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->toArray();

        if (! empty($pivot)) {
            DB::table('filemanagers_has_sites')->insert($pivot);
        }
    }
};
