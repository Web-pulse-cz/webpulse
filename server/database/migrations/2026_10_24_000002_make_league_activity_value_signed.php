<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow negative activity values (penalties)
        Schema::table('league_activities', function (Blueprint $table) {
            $table->integer('value')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('league_activities', function (Blueprint $table) {
            $table->unsignedInteger('value')->default(0)->change();
        });
    }
};
