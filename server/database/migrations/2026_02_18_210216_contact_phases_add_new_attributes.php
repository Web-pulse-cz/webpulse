<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_phases', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('color');
            $table->boolean('show_in_statistics')->default(true)->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('contact_phases', function (Blueprint $table) {
            $table->dropColumn(['position', 'show_in_statistics']);
        });
    }
};
