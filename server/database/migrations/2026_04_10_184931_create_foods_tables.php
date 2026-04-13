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
        // Table for allergens
        Schema::create('allergens', function (Blueprint $table) {
            $table->id();
            $table->integer('number');
            $table->timestamps();
        });

        // Table for allergen translations
        Schema::create('allergen_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('allergen_id');
            $table->foreign('allergen_id')->references('id')->on('allergens')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unique(['allergen_id', 'locale'], 'unique_allergen_translations');
            $table->timestamps();
        });

        // Table for foodstuff categories
        Schema::create('foodstuff_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foodstuff_category_id')->nullable();
            $table->foreign('foodstuff_category_id')->references('id')->on('foodstuff_categories')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });

        // Table for foodstuff category translations
        Schema::create('foodstuff_category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foodstuff_category_id');
            $table->foreign('foodstuff_category_id')->references('id')->on('foodstuff_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['foodstuff_category_id', 'locale'], 'unique_foodstuff_category_translations');
            $table->timestamps();
        });

        // Table for foodstuffs
        Schema::create('foodstuffs', function (Blueprint $table) {
            $table->id();
            $table->json('macronutrients')->nullable();
            $table->timestamps();
        });

        // Table for foodstuff translations
        Schema::create('foodstuff_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foodstuff_id');
            $table->foreign('foodstuff_id')->references('id')->on('foodstuffs')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['foodstuff_id', 'locale'], 'unique_foodstuff_translations');
            $table->timestamps();
        });

        // Schema for meal categories
        Schema::create('meal_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_category_id')->nullable();
            $table->foreign('meal_category_id')->references('id')->on('meal_categories')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });

        // Schema for meal category translations
        Schema::create('meal_category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_category_id');
            $table->foreign('meal_category_id')->references('id')->on('meal_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['meal_category_id', 'locale'], 'unique_meal_category_translations');
            $table->timestamps();
        });

        // Schema for meals
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Schema for meal translations
        Schema::create('meal_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_id');
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['meal_id', 'locale'], 'unique_meal_translations');
            $table->timestamps();
        });

        // Schema for receipe categories
        Schema::create('recipe_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Schema for recipe category translations
        Schema::create('recipe_category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_category_id');
            $table->foreign('recipe_category_id')->references('id')->on('recipe_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['recipe_category_id', 'locale'], 'unique_recipe_category_translations');
            $table->timestamps();
        });

        // Schema for recipes
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->integer('time_to_prepare')->nullable();
            $table->timestamps();
        });

        // Schema for recipe translations
        Schema::create('recipe_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['recipe_id', 'locale'], 'unique_recipe_translations');
            $table->timestamps();
        });

        // Schema for menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Schema for menu translations
        Schema::create('menu_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade')->onUpdate('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->unique(['menu_id', 'locale'], 'unique_menu_translations');
            $table->timestamps();
        });

        // Allergen-foodstuff pivot table
        Schema::create('allergen_foodstuff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('allergen_id');
            $table->foreign('allergen_id')->references('id')->on('allergens')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('foodstuff_id');
            $table->foreign('foodstuff_id')->references('id')->on('foodstuffs')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Allergen-meal pivot table
        Schema::create('allergen_meal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('allergen_id');
            $table->foreign('allergen_id')->references('id')->on('allergens')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('meal_id');
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Allergen-recipe pivot table
        Schema::create('allergen_recipe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('allergen_id');
            $table->foreign('allergen_id')->references('id')->on('allergens')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('recipe_id');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Foodstuff-category pivot table
        Schema::create('foodstuff_category_foodstuff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foodstuff_category_id');
            $table->foreign('foodstuff_category_id')->references('id')->on('foodstuff_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('foodstuff_id');
            $table->foreign('foodstuff_id')->references('id')->on('foodstuffs')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Foodstuff-meal pivot table
        Schema::create('foodstuff_meal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foodstuff_id');
            $table->foreign('foodstuff_id')->references('id')->on('foodstuffs')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('meal_id');
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Foodstuff-recipe pivot table
        Schema::create('foodstuff_recipe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foodstuff_id');
            $table->foreign('foodstuff_id')->references('id')->on('foodstuffs')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('recipe_id');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->onUpdate('cascade');
            $table->float('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        // Meal-category pivot table
        Schema::create('meal_category_meal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_category_id');
            $table->foreign('meal_category_id')->references('id')->on('meal_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('meal_id');
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Meal-menu pivot table
        Schema::create('meal_menu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_id');
            $table->foreign('meal_id')->references('id')->on('meals')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('menu_id');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });

        // Recipe-category pivot table
        Schema::create('recipe_category_recipe', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_category_id');
            $table->foreign('recipe_category_id')->references('id')->on('recipe_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('recipe_id');
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allergens');
        Schema::dropIfExists('allergen_translations');
        Schema::dropIfExists('foodstuff_categories');
        Schema::dropIfExists('foodstuff_category_translations');
        Schema::dropIfExists('foodstuffs');
        Schema::dropIfExists('foodstuff_translations');
        Schema::dropIfExists('meals');
        Schema::dropIfExists('meal_translations');
        Schema::dropIfExists('recipe_categories');
        Schema::dropIfExists('recipe_category_translations');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('recipe_translations');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_translations');
        Schema::dropIfExists('allergen_foodstuff');
        Schema::dropIfExists('allergen_meal');
        Schema::dropIfExists('allergen_recipe');
        Schema::dropIfExists('foodstuff_category_foodstuff');
        Schema::dropIfExists('foodstuff_meal');
        Schema::dropIfExists('foodstuff_recipe');
        Schema::dropIfExists('meal_category_meal');
        Schema::dropIfExists('meal_menu');
        Schema::dropIfExists('recipe_category_recipe');
    }
};
