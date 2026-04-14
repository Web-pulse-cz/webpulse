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
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('invoice_country');

            $table->unsignedBigInteger('invoice_country_id')->after('invoice_zip')->nullable();
            $table->foreign('invoice_country_id')->references('id')->on('countries')->onDelete('SET NULL')->onUpdate('CASCADE');

            $table->dropColumn('delivery_country');
            $table->unsignedBigInteger('delivery_country_id')->after('delivery_zip')->nullable();
            $table->foreign('delivery_country_id')->references('id')->on('countries')->onDelete('SET NULL')->onUpdate('CASCADE');

            $table->unsignedBigInteger('currency_id')->after('total_price_vat')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('SET NULL')->onUpdate('CASCADE');

            $table->decimal('expected_hours', 10, 2)->after('expected_price')->nullable();
            $table->decimal('total_hours', 10, 2)->after('total_price_vat')->nullable();

            $table->decimal('expected_price_vat', 10, 2)->after('expected_price')->nullable();

            $table->boolean('is_delivery_address_same')->after('invoice_country_id')->default(0);

            $table->unsignedBigInteger('status_id')->after('delivery_country_id')->nullable();
            $table->foreign('status_id')->references('id')->on('project_statuses')->onDelete('SET NULL')->onUpdate('CASCADE'); // TODO currency_id

            $table->decimal('hourly_rate', 10, 2)->after('image')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['invoice_country_id']);
            $table->dropColumn('invoice_country_id');

            $table->dropForeign(['delivery_country_id']);
            $table->dropColumn('delivery_country_id');

            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');

            $table->string('invoice_country')->after('invoice_zip')->nullable();
            $table->string('delivery_country')->after('delivery_zip')->nullable();
            $table->dropColumn('expected_hours');
            $table->dropColumn('total_hours');
            $table->dropColumn('is_delivery_address_same');
            $table->dropColumn('expected_price_vat');
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
            $table->dropColumn('hourly_rate');
        });
    }
};
