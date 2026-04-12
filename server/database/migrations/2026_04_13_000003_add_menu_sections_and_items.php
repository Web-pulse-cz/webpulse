<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		// Add price and weight to meals
		Schema::table('meals', function (Blueprint $table) {
			$table->decimal('price', 10, 2)->default(0)->after('id');
			$table->string('weight')->nullable()->after('price'); // e.g. "200g", "0.3l"
		});

		// Menu sections (Předkrm, Dezert, Polévky, ...)
		Schema::create('menu_sections', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('description')->nullable();
			$table->integer('position')->default(0);
			$table->timestamps();
		});

		// Menu items — individual dishes in a menu under a section
		Schema::create('menu_items', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('menu_id');
			$table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
			$table->unsignedBigInteger('section_id')->nullable();
			$table->foreign('section_id')->references('id')->on('menu_sections')->onDelete('set null');
			$table->unsignedBigInteger('meal_id')->nullable();
			$table->foreign('meal_id')->references('id')->on('meals')->onDelete('set null');

			// Fields — pre-filled from meal if linked, or custom
			$table->string('name');
			$table->text('description')->nullable();
			$table->decimal('price', 10, 2)->default(0);
			$table->string('weight')->nullable();
			$table->json('allergen_ids')->nullable(); // array of allergen IDs
			$table->integer('position')->default(0);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('menu_items');
		Schema::dropIfExists('menu_sections');

		Schema::table('meals', function (Blueprint $table) {
			$table->dropColumn(['price', 'weight']);
		});
	}
};
