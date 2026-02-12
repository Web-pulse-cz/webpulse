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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('name');
            $table->integer('position')->default(0);
            $table->boolean('is_main')->default(false);
            $table->morphs('imagable'); // This will create 'imagable_id' and 'imagable_type' columns

            $table->timestamps();
        });

        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->integer('position')->default(0)->change();
            $table->boolean('is_main')->default(false)->change();
            $table->morphs('filable'); // This will create 'filable_id' and 'filable_type' columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
        Schema::dropIfExists('files');
    }
};
