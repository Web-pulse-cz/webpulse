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
        Schema::create('contact_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('contacts_in_lists', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unsignedBigInteger('contact_list_id');
            $table->foreign('contact_list_id')
                ->references('id')
                ->on('contact_lists')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();

            $table->unique(['contact_id', 'contact_list_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_lists');
        Schema::dropIfExists('contacts_in_lists');
    }
};
