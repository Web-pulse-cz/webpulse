<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            // Make employee_id nullable (contract can exist without employee)
            $table->unsignedBigInteger('employee_id')->nullable()->change();

            // Add project_id for project-linked contracts
            $table->unsignedBigInteger('project_id')->nullable()->after('employee_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        // Add 'nda' to type enum
        DB::statement("ALTER TABLE employee_contracts MODIFY COLUMN type ENUM('hpp', 'dpp', 'dpc', 'osvc', 'internship', 'nda', 'other') DEFAULT 'other'");
    }

    public function down(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();
        });
    }
};
