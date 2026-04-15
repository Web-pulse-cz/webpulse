<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('billing_name')->nullable()->after('fakturoid_slug');
            $table->string('billing_ico')->nullable()->after('billing_name');
            $table->string('billing_dic')->nullable()->after('billing_ico');
            $table->string('billing_street')->nullable()->after('billing_dic');
            $table->string('billing_city')->nullable()->after('billing_street');
            $table->string('billing_zip')->nullable()->after('billing_city');
            $table->string('billing_bank_account')->nullable()->after('billing_zip');
            $table->string('billing_iban')->nullable()->after('billing_bank_account');
            $table->string('billing_swift')->nullable()->after('billing_iban');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'billing_name', 'billing_ico', 'billing_dic',
                'billing_street', 'billing_city', 'billing_zip',
                'billing_bank_account', 'billing_iban', 'billing_swift',
            ]);
        });
    }
};
