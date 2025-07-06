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
        Schema::create('faq_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('faq_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id')->constrained('faq_categories')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['faq_category_id', 'locale']);
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->integer('position')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('question');
            $table->text('answer');
            $table->unique(['faq_id', 'locale']);
        });

        Schema::create('faq_in_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faq_id');
            $table->unsignedBigInteger('faq_category_id');

            $table->foreign('faq_id')
                ->references('id')
                ->on('faqs')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('faq_category_id')
                ->references('id')
                ->on('faq_categories')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unique(['faq_id', 'faq_category_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('faq_category_translations');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('faq_in_categories');
    }
};
