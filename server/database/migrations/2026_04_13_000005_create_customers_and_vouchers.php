<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Zákaznické skupiny
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#6366f1');
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->unsignedBigInteger('discount_currency_id')->nullable();
            $table->foreign('discount_currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Zákazníci
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Osobní údaje
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone_prefix')->default('+420');
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Adresa
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            // Firemní údaje (volitelné)
            $table->string('company_name')->nullable();
            $table->string('ico')->nullable();
            $table->string('dic')->nullable();

            // Finanční údaje
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->decimal('credit_balance', 10, 2)->default(0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            // Interní hodnocení (1-5)
            $table->unsignedTinyInteger('rating')->nullable();

            // Skupina
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('set null');

            // Status
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');

            // Ostatní
            $table->text('note')->nullable();
            $table->timestamp('last_purchase_at')->nullable();

            $table->timestamps();
        });

        // Slevové vouchery
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            // Typ a hodnota slevy
            $table->enum('discount_type', ['fixed', 'percentage']);
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            // Minimální hodnota objednávky pro uplatnění
            $table->decimal('min_order_value', 10, 2)->nullable();

            // Platnost
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            // Počet použití
            $table->integer('max_uses')->nullable(); // null = neomezeno
            $table->integer('used_count')->default(0);
            $table->integer('max_uses_per_customer')->nullable(); // null = neomezeno

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // Voucher <-> Customer pivot
        Schema::create('voucher_customer', function (Blueprint $table) {
            $table->unsignedBigInteger('voucher_id');
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->integer('times_used')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->primary(['voucher_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_customer');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
    }
};
