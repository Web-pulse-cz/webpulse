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
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('event_category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_category_id');
            $table->foreign('event_category_id')->references('id')->on('event_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['event_category_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('place')->nullable();
            $table->boolean('is_online')->default(false);

            $table->integer('position')->default(0);
            $table->integer('max_participants')->default(0);
            $table->boolean('registration_required')->default(false);

            $table->decimal('price', 10, 2)->default(0.00);

            $table->timestamp('registration_from')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();

            $table->unsignedBigInteger('event_category_id')->nullable();
            $table->foreign('event_category_id')->references('id')->on('event_categories')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null')->onUpdate('cascade');

            $table->timestamps();
        });

        Schema::create('event_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['event_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade')->onUpdate('cascade');
            $table->string('email');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('phone')->nullable();
            $table->text('note')->nullable();

            $table->string('ico')->nullable();
            $table->string('dic')->nullable();
            $table->string('company')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null')->onUpdate('cascade');

            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('event_translation');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_category_translations');
        Schema::dropIfExists('event_categories');
    }
};
