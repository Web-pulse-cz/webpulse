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
        Schema::table('contact_histories', function (Blueprint $table) {
            $table->enum('origin', ['system', 'user', 'mentor', 'other'])
                ->after('description')
                ->default('other');

            $table->enum('type', ['call', 'email', 'meeting', 'activity', 'other'])
                ->after('origin')
                ->default('call');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_histories', function (Blueprint $table) {
            $table->dropColumn('origin');
            $table->dropColumn('type');
        });
    }
};
