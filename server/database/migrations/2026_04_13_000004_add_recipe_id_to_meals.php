<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->unsignedBigInteger('recipe_id')->nullable()->after('weight');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->dropColumn('recipe_id');
        });
    }
};
