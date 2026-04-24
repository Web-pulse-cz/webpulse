<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartment_types', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('apartment_type_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartment_type_id');
            $table->foreign('apartment_type_id')->references('id')->on('apartment_types')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['apartment_type_id', 'locale']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_type_translations');
        Schema::dropIfExists('apartment_types');
    }
};
