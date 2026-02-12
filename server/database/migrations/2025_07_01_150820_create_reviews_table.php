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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('rating')->default(0);
            $table->boolean('active')->default(true);
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->timestamps();
        });

        Schema::create('review_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->text('content');
            $table->unique(['review_id', 'locale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('review_translations');
    }
};
