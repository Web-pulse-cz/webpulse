<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_galleries', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('photo_gallery_translations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('photo_gallery_id');
            $table->foreign('photo_gallery_id')
                ->references('id')
                ->on('photo_galleries')
                ->onDelete('cascade');
            $table->string('locale')->index();

            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->unique(['photo_gallery_id', 'locale']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_gallery_translations');
        Schema::dropIfExists('photo_galleries');
    }
};
