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
        // contacts phases
        Schema::create('contact_phases', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // contacts categories
        Schema::create('contact_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });

        // contacts tasks
        Schema::create('contact_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('contact_phase_id')->nullable();
            $table->foreign('contact_phase_id')->references('id')->on('contact_phases')->onDelete('set null');

            $table->timestamps();
        });

        // contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone_prefix')->default('+420');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->string('company')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();

            $table->string('occupation')->nullable();
            $table->string('goal')->nullable();

            $table->string('note')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');

            $table->unsignedBigInteger('contact_source_id')->nullable();
            $table->foreign('contact_source_id')->references('id')->on('contact_sources')->onDelete('set null');

            $table->unsignedBigInteger('contact_phase_id')->nullable();
            $table->foreign('contact_phase_id')->references('id')->on('contact_phases')->onDelete('set null');

            $table->timestamp('next_meeting')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
        });

        // Contacts has tasks
        Schema::create('contacts_has_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->unsignedBigInteger('contact_task_id');
            $table->foreign('contact_task_id')->references('id')->on('contact_tasks')->onDelete('cascade');

            $table->timestamps();
        });

        // contacts history
        Schema::create('contact_histories', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('note')->nullable();

            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->unsignedBigInteger('contact_phase_id')->nullable();
            $table->foreign('contact_phase_id')->references('id')->on('contact_phases')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_phases');
        Schema::dropIfExists('contact_sources');
        Schema::dropIfExists('contact_tasks');
        Schema::dropIfExists('contacts_has_tasks');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('contact_histories');
    }
};
