<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartment_blocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade')->onUpdate('cascade');

            $table->date('start_date');
            $table->date('end_date');

            $table->enum('reason', ['maintenance', 'owner', 'other'])->default('other');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['apartment_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_blocks');
    }
};
