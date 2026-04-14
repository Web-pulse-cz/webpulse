<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->integer('position')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->unsignedBigInteger('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('project_statuses')->onDelete('set null');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
            $table->date('start_date')->nullable();
            $table->date('deadline_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('expected_hours', 10, 2)->default(0);
            $table->decimal('total_tracked_hours', 10, 2)->default(0);
            $table->decimal('expected_revenue', 10, 2)->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('total_costs', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        // Add FK from invoices.project_id now that projects table exists
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        Schema::create('project_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->primary(['project_id', 'tag_id']);
        });

        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('milestone_id')->nullable();
            $table->foreign('milestone_id')->references('id')->on('project_milestones')->onDelete('set null');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('project_time_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->foreign('task_id')->references('id')->on('project_tasks')->onDelete('set null');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('hours', 10, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->date('date');
            $table->timestamp('timer_started_at')->nullable();
            $table->boolean('is_running')->default(false);
            $table->timestamps();
        });

        Schema::create('project_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['software', 'hardware', 'subcontractor', 'marketing', 'travel', 'other'])->default('other');
            $table->decimal('amount', 10, 2)->default(0);
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->date('date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->timestamps();
        });

        Schema::create('project_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
        Schema::dropIfExists('project_notes');
        Schema::dropIfExists('project_costs');
        Schema::dropIfExists('project_time_entries');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_tag');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('project_statuses');
    }
};
