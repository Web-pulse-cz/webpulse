<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
        Schema::table('logos', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
        Schema::table('novelties', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['image']);
        });
    }

    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->string('image');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->string('image');
        });
        Schema::table('logos', function (Blueprint $table) {
            $table->string('image');
        });
        Schema::table('novelties', function (Blueprint $table) {
            $table->string('image');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image');
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->string('image');
        });
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('image');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->string('image');
        });
    }
};
