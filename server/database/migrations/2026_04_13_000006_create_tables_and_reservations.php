<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stoly
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('name')->nullable();
            $table->integer('seats')->default(2);
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'occupied', 'reserved', 'maintenance'])->default('available');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Rezervace
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id');
            $table->foreign('table_id')->references('id')->on('restaurant_tables')->onDelete('cascade');

            $table->date('date');
            $table->time('time_from');
            $table->time('time_to')->nullable();

            $table->string('guest_first_name');
            $table->string('guest_last_name');
            $table->string('guest_phone_prefix')->default('+420');
            $table->string('guest_phone')->nullable();
            $table->string('guest_email')->nullable();
            $table->integer('guests_count')->default(2);

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');

            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->enum('source', ['manual', 'web', 'phone', 'email'])->default('manual');

            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('restaurant_tables');
    }
};
