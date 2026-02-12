<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('novelties', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('priority')->default(1);
            $table->timestamps();
        });

        // create translations table
        Schema::create('novelty_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('novelty_id');
            $table->foreign('novelty_id')
                ->references('id')
                ->on('novelties')
                ->onDelete('cascade');

            $table->string('locale')->index();

            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['novelty_id', 'locale']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
