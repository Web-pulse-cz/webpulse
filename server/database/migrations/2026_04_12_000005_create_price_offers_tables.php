<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_offers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            $table->string('title')->nullable();
            $table->text('introduction')->nullable();
            $table->text('note')->nullable();
            $table->text('terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
            $table->decimal('total_without_vat', 10, 2)->default(0);
            $table->decimal('total_vat', 10, 2)->default(0);
            $table->decimal('total_with_vat', 10, 2)->default(0);
            $table->date('valid_to')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('price_offer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_offer_id');
            $table->foreign('price_offer_id')->references('id')->on('price_offers')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 3)->default(1);
            $table->string('unit_name')->nullable();
            $table->decimal('unit_price_without_vat', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('total_without_vat', 10, 2)->default(0);
            $table->decimal('total_vat', 10, 2)->default(0);
            $table->decimal('total_with_vat', 10, 2)->default(0);
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Add FK from invoices.price_offer_id now that price_offers table exists
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('price_offer_id')->references('id')->on('price_offers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['price_offer_id']);
        });
        Schema::dropIfExists('price_offer_items');
        Schema::dropIfExists('price_offers');
    }
};
