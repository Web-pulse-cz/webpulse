<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		// Add prefix to projects for task code generation
		Schema::table('projects', function (Blueprint $table) {
			$table->string('prefix', 5)->nullable()->unique()->after('name');
		});

		// Task categories per project (Frontend, Backend, Design, ...)
		Schema::create('project_task_categories', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('project_id');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->string('name');
			$table->string('color')->default('#6366f1');
			$table->integer('position')->default(0);
			$table->timestamps();
		});

		// Task boards per project (Todo, In Progress, Done, ...)
		Schema::create('project_task_boards', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('project_id');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->string('name');
			$table->string('color')->default('#6366f1');
			$table->boolean('is_completed')->default(false);
			$table->integer('position')->default(0);
			$table->timestamps();
		});

		// Alter project_tasks: add code, category_id, board_id; drop status enum
		Schema::table('project_tasks', function (Blueprint $table) {
			$table->string('code', 20)->nullable()->unique()->after('id');
			$table->unsignedBigInteger('category_id')->nullable()->after('project_id');
			$table->foreign('category_id')->references('id')->on('project_task_categories')->onDelete('set null');
			$table->unsignedBigInteger('board_id')->nullable()->after('category_id');
			$table->foreign('board_id')->references('id')->on('project_task_boards')->onDelete('set null');
			$table->dropColumn('status');
		});

		// Task assignees pivot (multiple users per task)
		Schema::create('project_task_assignees', function (Blueprint $table) {
			$table->unsignedBigInteger('task_id');
			$table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('cascade');
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->primary(['task_id', 'user_id']);
		});

		// Task comments
		Schema::create('project_task_comments', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('task_id');
			$table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('cascade');
			$table->unsignedBigInteger('user_id')->nullable();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
			$table->text('content');
			$table->timestamps();
		});

		// Make project_id nullable on time entries (standalone tracking)
		Schema::table('project_time_entries', function (Blueprint $table) {
			$table->unsignedBigInteger('project_id')->nullable()->change();
		});

		// Sequence table for task code generation (atomic)
		Schema::create('project_task_code_sequences', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('project_id')->nullable()->unique();
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->unsignedBigInteger('last_number')->default(0);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('project_task_code_sequences');
		Schema::dropIfExists('project_task_comments');
		Schema::dropIfExists('project_task_assignees');

		Schema::table('project_tasks', function (Blueprint $table) {
			$table->dropForeign(['category_id']);
			$table->dropForeign(['board_id']);
			$table->dropColumn(['code', 'category_id', 'board_id']);
			$table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
		});

		Schema::dropIfExists('project_task_boards');
		Schema::dropIfExists('project_task_categories');

		Schema::table('projects', function (Blueprint $table) {
			$table->dropColumn('prefix');
		});

		Schema::table('project_time_entries', function (Blueprint $table) {
			$table->unsignedBigInteger('project_id')->nullable(false)->change();
		});
	}
};
