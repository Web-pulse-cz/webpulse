<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });

        Schema::table('event_category_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });

        Schema::table('event_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });

        Schema::table('novelty_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });

        Schema::table('post_category_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });

        Schema::table('post_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });

        Schema::table('post_translations', function (Blueprint $table) {
            $table->longText('text')->change();
        });
    }

    public function down(): void
    {
        Schema::table('career_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });

        Schema::table('event_category_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });

        Schema::table('event_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });

        Schema::table('novelty_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });

        Schema::table('post_category_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });

        Schema::table('post_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });

        Schema::table('post_translations', function (Blueprint $table) {
            $table->text('text')->change();
        });
    }
};
