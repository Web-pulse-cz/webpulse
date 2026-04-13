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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->integer('fakturoid_id')->nullable();

            $table->unsignedBigInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->enum('type', ['customer', 'supplier', 'both'])->default('customer');
            $table->string('name');
            $table->string('full_name')->nullable();
            $table->string('email')->nullable();
            $table->string('email_copy')->nullable();
            $table->string('phone_prefix')->default('+420');
            $table->string('phone')->nullable();

            $table->string('ico')->nullable();
            $table->string('dic')->nullable();

            $table->string('web')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();

            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            $table->boolean('has_delivery_address')->default(false);
            $table->string('delivery_name')->nullable();
            $table->string('delivery_street')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_zip')->nullable();
            $table->unsignedBigInteger('delivery_country_id')->nullable();
            $table->foreign('delivery_country_id')->references('id')->on('countries')->onDelete('set null');

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->unsignedBigInteger('language_id')->nullable();
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');

            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_iban')->nullable();
            $table->string('bank_account_swift')->nullable();
            $table->string('variable_symbol')->nullable();

            $table->string('note')->nullable();

            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->integer('fakturoid_id')->nullable();

            $table->unsignedBigInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');

            $table->enum('document_type', ['partial_proforma', 'proforma', 'final_invoice', 'invoice'])->default('invoice');

            $table->string('number');

            $table->decimal('subtotal', 10, 6)->default(0);
            $table->decimal('total', 10, 6)->default(0);

            $table->string('filename')->nullable();
            $table->string('external_file')->nullable();

            $table->enum('status', ['open', 'sent', 'overdue', 'paid', 'cancelled'])->default('open');

            $table->string('note')->nullable();

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->unsignedBigInteger('language_id')->nullable();
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');

            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');

            $table->timestamp('issued_on')->nullable();
            $table->timestamp('due_on')->nullable();
            $table->timestamp('paid_on')->nullable();
            $table->timestamp('cancelled_on')->nullable();
            $table->timestamp('sent_on')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->integer('quantity')->default(1);

            $table->string('unit_name')->nullable();
            $table->decimal('unit_price', 10, 6)->default(0);

            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');

            $table->decimal('unit_price_without_vat', 10, 6)->default(0);
            $table->decimal('unit_price_with_vat', 10, 6)->default(0);
            $table->decimal('total_price_without_vat', 10, 6)->default(0);
            $table->decimal('total_vat', 10, 6)->default(0);
            $table->decimal('native_total_price_without_vat', 10, 6)->default(0);
            $table->decimal('native_total_vat', 10, 6)->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
