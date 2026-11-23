<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Přidá typ 'last_contact' – systémový záznam předchozí hodnoty „Naposledy kontaktováno“.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE contact_histories MODIFY type ENUM('call', 'email', 'meeting', 'activity', 'other', 'last_contact') NOT NULL DEFAULT 'call'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('contact_histories')->where('type', 'last_contact')->update(['type' => 'other']);
        DB::statement("ALTER TABLE contact_histories MODIFY type ENUM('call', 'email', 'meeting', 'activity', 'other') NOT NULL DEFAULT 'call'");
    }
};
