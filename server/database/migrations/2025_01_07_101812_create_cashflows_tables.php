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
        Schema::create('cashflow_categories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->string('name');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('cashflow_budgets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cashflow_category_id')->nullable();
            $table->foreign('cashflow_category_id')->references('id')->on('cashflow_categories')->onDelete('cascade')->onUpdate('cascade');

            /*$table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');*/

            $table->enum('type', ['year', 'month'])->default('month');
            $table->decimal('amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date');

            $table->timestamps();
        });

        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cashflow_category_id');
            $table->foreign('cashflow_category_id')->references('id')->on('cashflow_categories')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->decimal('amount', 10, 2);
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->string('description');
            $table->date('date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflow_categories');
        Schema::dropIfExists('cashflow_budgets');
        Schema::dropIfExists('cashflows');
    }
};
