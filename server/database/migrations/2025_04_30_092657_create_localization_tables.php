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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->string('code')->nullable();
            $table->string('iso')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();
        });

        Schema::create('country_translations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');

            $table->string('locale');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();

            $table->string('code')->nullable();
            $table->string('iso')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();
        });

        Schema::create('language_translations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');

            $table->string('locale');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            $table->string('code')->nullable();
            $table->decimal('rate', 10, 6)->nullable()->default(1);
            $table->boolean('active')->default(true);

            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_iban')->nullable();
            $table->string('bank_account_swift')->nullable();

            $table->timestamps();
        });

        Schema::create('currency_translations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');

            $table->string('locale');
            $table->string('name');
            $table->string('symbol_before')->nullable();
            $table->string('symbol_after')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
        Schema::dropIfExists('country_translations');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('language_translations');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('currency_translations');
    }
};
