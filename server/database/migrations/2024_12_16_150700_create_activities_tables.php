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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('users_has_activities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('activity_id');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');

            $table->string('description')->nullable();

            $table->decimal('duration', 8, 2)->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });

        Schema::table('contact_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('activity_id')->nullable()->after('contact_phase_id');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('activities_tables');
        Schema::dropIfExists('users_has_activities');

        Schema::table('contact_histories', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropColumn('activity_id');
        });
    }
};
