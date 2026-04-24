<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartment_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();

            $table->unsignedBigInteger('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade')->onUpdate('cascade');

            $table->date('start_date');
            $table->date('end_date');

            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->enum('source', ['admin', 'web', 'booking'])->default('admin');

            $table->string('guest_firstname');
            $table->string('guest_lastname');
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->integer('number_of_guests')->default(1);

            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null')->onUpdate('cascade');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['apartment_id', 'start_date', 'end_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_reservations');
    }
};
