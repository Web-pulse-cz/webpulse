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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->string('image')->nullable();
            $table->integer('position')->default(0);
            $table->enum('type', ['full-time', 'part-time', 'internship', 'freelance', 'volunteer', 'all'])->default('full-time');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('salary_from', 10, 2)->nullable();
            $table->decimal('salary_to', 10, 2)->nullable();
            $table->enum('salary_type', ['hourly', 'monthly'])->default('monthly');
            $table->enum('start_date', ['immediate', 'negotiable'])->default('immediate');

            $table->timestamps();
        });

        Schema::create('career_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('career_id');
            $table->foreign('career_id')->references('id')->on('careers')->onDelete('cascade')->onUpdate('cascade');

            $table->string('locale')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('perex')->nullable();
            $table->text('text')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('location')->nullable();
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();

            $table->unique(['career_id', 'locale']);
            $table->timestamps();
        });

        Schema::create('career_applications', function (Blueprint $table) {
            $table->id();

            $table->string('firstname');
            $table->string('lastname');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume')->nullable(); // for file upload
            $table->enum('status', ['pending', 'reviewed', 'accepted', 'rejected'])->default('pending');
            $table->decimal('salary_expectation', 10, 2)->nullable();
            $table->enum('availability', ['immediate', '1-week', '2-weeks', '1-month', '2-month', 'negotiable'])->default('immediate');
            $table->enum('source', ['website', 'referral', 'social_media', 'job_board', 'other'])->default('website');
            $table->string('locale')->nullable();

            $table->unsignedBigInteger('career_id');
            $table->foreign('career_id')->references('id')->on('careers')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('user_id')->nullable(); // to track who manages the application
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_applications');
        Schema::dropIfExists('career_translations');
        Schema::dropIfExists('careers');
    }
};
