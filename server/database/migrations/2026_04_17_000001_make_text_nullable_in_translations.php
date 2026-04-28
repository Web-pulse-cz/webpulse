<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_translations', function (Blueprint $table) {
            $table->longText('text')->nullable()->change();
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->longText('text')->nullable()->change();
        });

        Schema::table('novelty_translations', function (Blueprint $table) {
            $table->longText('text')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('post_translations', function (Blueprint $table) {
            $table->longText('text')->nullable(false)->change();
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->longText('text')->nullable(false)->change();
        });

        Schema::table('novelty_translations', function (Blueprint $table) {
            $table->longText('text')->nullable(false)->change();
        });
    }
};
