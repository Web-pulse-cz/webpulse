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
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('name')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('logo_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logo_id')->constrained('logos')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('url')->nullable();
            $table->unique(['logo_id', 'locale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logos');
        Schema::dropIfExists('logo_translations');
    }
};
