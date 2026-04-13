<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		// Convert existing hours to seconds, then rename column
		// First add new column
		Schema::table('project_time_entries', function (Blueprint $table) {
			$table->unsignedInteger('seconds')->default(0)->after('hours');
		});

		// Convert existing data (hours → seconds)
		DB::table('project_time_entries')
			->whereNotNull('hours')
			->update(['seconds' => DB::raw('ROUND(hours * 3600)')]);

		// Drop old column
		Schema::table('project_time_entries', function (Blueprint $table) {
			$table->dropColumn('hours');
		});

		// Also change total_tracked_hours on projects to total_tracked_seconds
		Schema::table('projects', function (Blueprint $table) {
			$table->unsignedInteger('total_tracked_seconds')->default(0)->after('total_tracked_hours');
		});

		DB::table('projects')
			->whereNotNull('total_tracked_hours')
			->update(['total_tracked_seconds' => DB::raw('ROUND(total_tracked_hours * 3600)')]);

		Schema::table('projects', function (Blueprint $table) {
			$table->dropColumn('total_tracked_hours');
		});
	}

	public function down(): void
	{
		Schema::table('projects', function (Blueprint $table) {
			$table->decimal('total_tracked_hours', 10, 2)->default(0)->after('total_tracked_seconds');
		});

		DB::table('projects')->update(['total_tracked_hours' => DB::raw('ROUND(total_tracked_seconds / 3600, 2)')]);

		Schema::table('projects', function (Blueprint $table) {
			$table->dropColumn('total_tracked_seconds');
		});

		Schema::table('project_time_entries', function (Blueprint $table) {
			$table->decimal('hours', 10, 2)->default(0)->after('seconds');
		});

		DB::table('project_time_entries')->update(['hours' => DB::raw('ROUND(seconds / 3600, 2)')]);

		Schema::table('project_time_entries', function (Blueprint $table) {
			$table->dropColumn('seconds');
		});
	}
};
