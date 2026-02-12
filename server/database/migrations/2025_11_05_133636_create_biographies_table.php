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
        Schema::create('biographies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('job_title')->nullable();
            $table->string('template')->default('default');
            $table->string('phone_prefix')->default('+420');
            $table->string('phone');
            $table->string('email');
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->text('about_me')->nullable();
            $table->text('summary')->nullable();
            $table->json('job_experiences')->nullable();
            $table->json('education')->nullable();
            $table->json('skills')->nullable();
            $table->json('hard_skills')->nullable();
            $table->json('soft_skills')->nullable();
            $table->string('filename')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biographies');
    }
};
