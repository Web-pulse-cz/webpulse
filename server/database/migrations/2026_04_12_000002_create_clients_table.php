<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fakturoid_id')->nullable()->unique();
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
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_iban')->nullable();
            $table->string('bank_account_swift')->nullable();
            $table->string('variable_symbol')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('fakturoid_updated_at')->nullable();
            $table->timestamp('local_updated_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
