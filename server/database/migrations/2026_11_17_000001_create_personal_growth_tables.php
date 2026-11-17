<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Osobní růst — knihy, audia, semináře. Čistě per-user (user_id), bez Siteable.
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->string('author')->nullable();
            $table->longText('notes')->nullable();
            $table->json('keywords')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('audios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->string('speaker')->nullable();
            $table->longText('notes')->nullable();
            $table->json('keywords')->nullable();
            $table->timestamps();
        });

        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('place')->nullable();
            $table->date('date')->nullable();
            $table->string('organizer')->nullable();
            $table->enum('type', ['seminar', 'training', 'leadership'])->default('seminar');
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        // Úkoly ke knihám a seminářům (polymorfně, hotové úkoly zůstávají viditelné).
        Schema::create('personal_growth_tasks', function (Blueprint $table) {
            $table->id();
            $table->morphs('taskable');
            $table->string('title', 500);
            $table->boolean('is_done')->default(false);
            $table->timestamp('done_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_growth_tasks');
        Schema::dropIfExists('seminars');
        Schema::dropIfExists('audios');
        Schema::dropIfExists('books');
    }
};
