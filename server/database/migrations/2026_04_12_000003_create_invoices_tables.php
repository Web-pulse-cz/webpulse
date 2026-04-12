<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fakturoid_id')->nullable()->unique();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->unsignedBigInteger('project_id')->nullable();
            // project_id FK added in projects migration
            $table->unsignedBigInteger('price_offer_id')->nullable();
            // price_offer_id FK added in price_offers migration
            $table->enum('document_type', ['proforma', 'partial_proforma', 'final_invoice', 'invoice'])->default('invoice');
            $table->string('number')->nullable();
            $table->string('subject')->nullable();
            $table->text('note')->nullable();
            $table->text('footer_note')->nullable();
            $table->enum('status', ['open', 'sent', 'overdue', 'paid', 'cancelled'])->default('open');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('total_vat', 10, 2)->default(0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('set null');
            $table->enum('payment_method', ['bank', 'cash', 'card', 'paypal'])->default('bank');
            $table->string('variable_symbol')->nullable();
            $table->string('constant_symbol')->nullable();
            $table->string('specific_symbol')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift_bic')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('taxable_fulfillment_due')->nullable();
            $table->date('due_on')->nullable();
            $table->date('paid_on')->nullable();
            $table->date('cancelled_on')->nullable();
            $table->date('sent_on')->nullable();
            $table->timestamp('fakturoid_updated_at')->nullable();
            $table->timestamp('local_updated_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->string('name');
            $table->decimal('quantity', 10, 3)->default(1);
            $table->string('unit_name')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('total_without_vat', 10, 2)->default(0);
            $table->decimal('total_vat', 10, 2)->default(0);
            $table->decimal('total_with_vat', 10, 2)->default(0);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
