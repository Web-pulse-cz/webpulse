<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		// Global task boards (nezávislé na projektu, Siteable)
		Schema::create('task_boards', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('color')->default('#6366f1');
			$table->boolean('is_completed')->default(false);
			$table->integer('position')->default(0);
			$table->timestamps();
		});

		// Make project_id nullable on tasks (standalone tasks)
		Schema::table('project_tasks', function (Blueprint $table) {
			$table->unsignedBigInteger('project_id')->nullable()->change();
			$table->unsignedBigInteger('site_id')->nullable()->after('id');
			$table->foreign('site_id')->references('id')->on('sites')->onDelete('set null');
			// Global board reference (separate from project-level board_id)
			$table->unsignedBigInteger('global_board_id')->nullable()->after('board_id');
			$table->foreign('global_board_id')->references('id')->on('task_boards')->onDelete('set null');
		});
	}

	public function down(): void
	{
		Schema::table('project_tasks', function (Blueprint $table) {
			$table->dropForeign(['site_id']);
			$table->dropForeign(['global_board_id']);
			$table->dropColumn(['site_id', 'global_board_id']);
			$table->unsignedBigInteger('project_id')->nullable(false)->change();
		});

		Schema::dropIfExists('task_boards');
	}
};
