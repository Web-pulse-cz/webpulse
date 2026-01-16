<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('page_id');
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('cascade');
            $table->string('locale')->index();

            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->unique(['page_id', 'locale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
};
