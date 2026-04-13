<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fakturoid credentials per site
        Schema::table('sites', function (Blueprint $table) {
            $table->string('fakturoid_client_id')->nullable()->after('is_active');
            $table->string('fakturoid_client_secret')->nullable()->after('fakturoid_client_id');
            $table->string('fakturoid_slug')->nullable()->after('fakturoid_client_secret');
        });

        // Time entries — add site_id for auto-assignment
        Schema::table('project_time_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id')->nullable()->after('id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('project_time_entries', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['fakturoid_client_id', 'fakturoid_client_secret', 'fakturoid_slug']);
        });
    }
};
