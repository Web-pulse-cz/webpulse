<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('site_id');
            $table->string('widget_key');
            $table->unsignedInteger('position')->default(0);
            $table->string('size', 16)->default('half');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->unique(['user_id', 'site_id', 'widget_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_widgets');
    }
};
