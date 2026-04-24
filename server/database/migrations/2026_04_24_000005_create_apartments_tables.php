<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->unsignedBigInteger('apartment_type_id')->nullable();
            $table->foreign('apartment_type_id')->references('id')->on('apartment_types')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedBigInteger('building_id')->nullable();
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null')->onUpdate('cascade');

            $table->integer('capacity')->default(0);
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->decimal('area', 8, 2)->nullable();
            $table->integer('floor')->nullable();

            $table->decimal('base_price', 10, 2)->default(0.00);
            $table->integer('position')->default(0);

            $table->timestamps();
        });

        Schema::create('apartment_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['apartment_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('apartments_has_amenities', function (Blueprint $table) {
            $table->unsignedBigInteger('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('amenity_id');
            $table->foreign('amenity_id')->references('id')->on('amenities')->onDelete('cascade')->onUpdate('cascade');
            $table->primary(['apartment_id', 'amenity_id']);
        });

        Schema::create('apartment_season_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apartment_id');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('season_id');
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->unique(['apartment_id', 'season_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_season_prices');
        Schema::dropIfExists('apartments_has_amenities');
        Schema::dropIfExists('apartment_translations');
        Schema::dropIfExists('apartments');
    }
};
