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
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('text');
            $table->string('note')->nullable();
            $table->string('image')->nullable();

            $table->decimal('expected_price', 10, 2);
            $table->decimal('total_price', 10, 2)->nullable();
            $table->decimal('total_price_vat', 10, 2)->nullable();

            $table->string('invoice_firstname')->nullable();
            $table->string('invoice_lastname')->nullable();
            $table->string('invoice_ico')->nullable();
            $table->string('invoice_dic')->nullable();
            $table->string('invoice_email')->nullable();
            $table->string('invoice_phone_prefix')->nullable();
            $table->string('invoice_phone')->nullable();
            $table->string('invoice_street')->nullable();
            $table->string('invoice_city')->nullable();
            $table->string('invoice_zip')->nullable();
            $table->string('invoice_country')->nullable();

            $table->string('delivery_firstname')->nullable();
            $table->string('delivery_lastname')->nullable();
            $table->string('delivery_email')->nullable();
            $table->string('delivery_phone_prefix')->nullable();
            $table->string('delivery_phone')->nullable();
            $table->string('delivery_street')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_zip')->nullable();
            $table->string('delivery_country')->nullable();

            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');

            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('contacts')->onDelete('set null');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_statuses');
        Schema::dropIfExists('projects');
    }
};
