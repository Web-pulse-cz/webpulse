<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['service', 'product'])->default('service');
            $table->enum('price_type', ['hourly', 'total'])->default('hourly');
            $table->decimal('price', 10, 2)->default(0.00);

            $table->unsignedBigInteger('tax_rate_id')->default(1);
            $table->foreign('tax_rate_id')
                ->references('id')
                ->on('tax_rates')
                ->onDelete('cascade');

            $table->unsignedBigInteger('currency_id')->default(1);
            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('cascade');

            $table->string('image')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();
        });

        Schema::create('service_translations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('service_id');

            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('description')->nullable(); // TODO: rename to 'text' in the future

            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();

            $table->unique(['service_id', 'locale']);
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services_tables');
        Schema::dropIfExists('service_translations');
    }
};
