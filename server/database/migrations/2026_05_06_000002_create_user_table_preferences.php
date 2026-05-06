<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_table_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('table_slug', 64);
            $table->json('visible_columns')->nullable();
            $table->unsignedSmallInteger('per_page')->default(25);
            $table->timestamps();

            $table->unique(['user_id', 'site_id', 'table_slug'], 'utp_unique');
            $table->index(['user_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_table_preferences');
    }
};
