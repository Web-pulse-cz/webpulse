<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		// Divisions (organizační jednotky)
		Schema::create('employee_divisions', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->text('description')->nullable();
			$table->string('color')->default('#6366f1');
			$table->string('address')->nullable();
			$table->string('city')->nullable();
			$table->string('zip')->nullable();
			$table->string('phone')->nullable();
			$table->string('email')->nullable();
			$table->unsignedBigInteger('head_employee_id')->nullable();
			// FK added after employees table exists
			$table->integer('position')->default(0);
			$table->timestamps();
		});

		// Employees
		Schema::create('employees', function (Blueprint $table) {
			$table->id();

			// Osobní údaje
			$table->string('first_name');
			$table->string('last_name');
			$table->string('email')->nullable();
			$table->string('phone_prefix')->default('+420');
			$table->string('phone')->nullable();
			$table->date('date_of_birth')->nullable();
			$table->enum('gender', ['male', 'female', 'other'])->nullable();
			$table->string('personal_id_number')->nullable(); // rodné číslo
			$table->string('id_card_number')->nullable(); // číslo OP
			$table->string('nationality')->nullable();

			// Adresa
			$table->string('street')->nullable();
			$table->string('city')->nullable();
			$table->string('zip')->nullable();
			$table->unsignedBigInteger('country_id')->nullable();
			$table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

			// Pracovní údaje
			$table->string('position')->nullable(); // pracovní pozice
			$table->string('employee_number')->nullable()->unique(); // osobní číslo
			$table->enum('status', ['active', 'on_leave', 'terminated', 'suspended'])->default('active');
			$table->date('date_hired')->nullable();
			$table->date('date_terminated')->nullable();
			$table->decimal('hourly_rate', 10, 2)->default(0);
			$table->decimal('monthly_salary', 10, 2)->default(0);
			$table->unsignedBigInteger('currency_id')->nullable();
			$table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

			// Bankovní údaje
			$table->string('bank_account_number')->nullable();
			$table->string('bank_account_iban')->nullable();
			$table->string('bank_account_swift')->nullable();

			// Zdravotní pojištění
			$table->string('health_insurance_company')->nullable();
			$table->string('health_insurance_number')->nullable();

			// Nouzový kontakt
			$table->string('emergency_contact_name')->nullable();
			$table->string('emergency_contact_phone')->nullable();
			$table->string('emergency_contact_relation')->nullable();

			// Ostatní
			$table->string('photo')->nullable(); // cesta k fotce
			$table->text('note')->nullable();

			$table->timestamps();
		});

		// FK pro head_employee na divisions
		Schema::table('employee_divisions', function (Blueprint $table) {
			$table->foreign('head_employee_id')->references('id')->on('employees')->onDelete('set null');
		});

		// Employee <-> Division pivot
		Schema::create('employee_division_employee', function (Blueprint $table) {
			$table->unsignedBigInteger('employee_id');
			$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
			$table->unsignedBigInteger('division_id');
			$table->foreign('division_id')->references('id')->on('employee_divisions')->onDelete('cascade');
			$table->primary(['employee_id', 'division_id']);
		});

		// Smlouvy
		Schema::create('employee_contracts', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('employee_id');
			$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
			$table->string('title');
			$table->text('description')->nullable();
			$table->enum('type', ['hpp', 'dpp', 'dpc', 'osvc', 'internship', 'other'])->default('hpp');
			$table->enum('status', ['draft', 'active', 'terminated', 'expired'])->default('draft');
			$table->date('date_from')->nullable();
			$table->date('date_to')->nullable(); // null = na dobu neurčitou
			$table->decimal('salary', 10, 2)->default(0);
			$table->enum('salary_type', ['monthly', 'hourly'])->default('monthly');
			$table->unsignedBigInteger('currency_id')->nullable();
			$table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
			$table->string('file_path')->nullable(); // uploaded PDF
			$table->text('content')->nullable(); // generated contract text
			$table->string('signed_by_employee')->nullable();
			$table->date('signed_at')->nullable();
			$table->text('terms')->nullable();
			$table->text('benefits')->nullable();
			$table->integer('vacation_days')->default(20);
			$table->integer('notice_period_days')->default(60);
			$table->text('note')->nullable();
			$table->timestamps();
		});

		// Šablony směn
		Schema::create('shift_templates', function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('color')->default('#6366f1');
			$table->time('start_time');
			$table->time('end_time');
			$table->integer('break_minutes')->default(30);
			$table->text('note')->nullable();
			$table->timestamps();
		});

		// Směny
		Schema::create('shifts', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('shift_template_id')->nullable();
			$table->foreign('shift_template_id')->references('id')->on('shift_templates')->onDelete('set null');
			$table->date('date');
			$table->time('start_time');
			$table->time('end_time');
			$table->integer('break_minutes')->default(30);
			$table->string('location')->nullable();
			$table->text('note')->nullable();
			$table->timestamps();
		});

		// Shift <-> Employee pivot
		Schema::create('shift_employee', function (Blueprint $table) {
			$table->unsignedBigInteger('shift_id');
			$table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
			$table->unsignedBigInteger('employee_id');
			$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
			$table->enum('status', ['scheduled', 'confirmed', 'completed', 'absent', 'cancelled'])->default('scheduled');
			$table->text('note')->nullable();
			$table->primary(['shift_id', 'employee_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('shift_employee');
		Schema::dropIfExists('shifts');
		Schema::dropIfExists('shift_templates');
		Schema::dropIfExists('employee_contracts');
		Schema::dropIfExists('employee_division_employee');

		Schema::table('employee_divisions', function (Blueprint $table) {
			$table->dropForeign(['head_employee_id']);
		});

		Schema::dropIfExists('employees');
		Schema::dropIfExists('employee_divisions');
	}
};
