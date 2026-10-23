<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            // points = activity value is points, minutes = activity value is seconds (displayed as minutes)
            $table->enum('scoring_type', ['points', 'minutes'])->default('points');
            $table->enum('visibility', ['public', 'invite'])->default('public');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('league_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $table->index(['league_id', 'position']);
        });

        Schema::create('league_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->unsignedBigInteger('league_section_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('value')->default(0);
            // null = unlimited records per day
            $table->unsignedInteger('max_per_day')->nullable()->default(1);
            $table->boolean('counts_to_league')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $table->foreign('league_section_id')->references('id')->on('league_sections')->onDelete('set null');
            $table->index(['league_id', 'league_section_id', 'position']);
        });

        Schema::create('league_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['league_id', 'user_id']);
        });

        Schema::create('league_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->unsignedBigInteger('league_activity_id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->unsignedInteger('count')->default(1);
            $table->timestamps();

            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $table->foreign('league_activity_id')->references('id')->on('league_activities')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['league_activity_id', 'user_id', 'date']);
            $table->index(['league_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_entries');
        Schema::dropIfExists('league_members');
        Schema::dropIfExists('league_activities');
        Schema::dropIfExists('league_sections');
        Schema::dropIfExists('leagues');
    }
};
