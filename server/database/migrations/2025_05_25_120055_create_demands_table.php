<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('demands', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('email');
            $table->string('phone_prefix');
            $table->string('phone');
            $table->string('url')->nullable();
            $table->string('text')->nullable();

            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('set null');
            $table->decimal('offer_price', 10, 2)->nullable();
            $table->string('locale')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demands');
    }
};
