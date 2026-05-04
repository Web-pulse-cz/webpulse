<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('site_id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade')->onUpdate('cascade');

            $table->string('blockable_type');
            $table->unsignedBigInteger('blockable_id');
            $table->index(['blockable_type', 'blockable_id']);

            $table->string('type', 64);
            $table->json('data')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['site_id', 'blockable_type', 'blockable_id', 'position']);
        });

        Schema::create('block_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('block_id');
            $table->foreign('block_id')->references('id')->on('blocks')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->json('data')->nullable();
            $table->unique(['block_id', 'locale']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_translations');
        Schema::dropIfExists('blocks');
    }
};
