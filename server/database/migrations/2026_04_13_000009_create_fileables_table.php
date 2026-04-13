<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('fileables', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('original_name')->nullable();
			$table->string('path');
			$table->string('disk')->default('public');
			$table->string('mime_type')->nullable();
			$table->unsignedBigInteger('size')->default(0); // bytes
			$table->string('fileable_type');
			$table->unsignedBigInteger('fileable_id');
			$table->index(['fileable_type', 'fileable_id']);
			$table->integer('position')->default(0);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('fileables');
	}
};
