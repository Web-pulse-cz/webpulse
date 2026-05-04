<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropIndex(['site_id', 'blockable_type', 'blockable_id', 'position']);
            $table->dropIndex(['blockable_type', 'blockable_id']);
            $table->dropColumn(['site_id', 'blockable_type', 'blockable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id')->after('id');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade')->onUpdate('cascade');
            $table->string('blockable_type')->after('site_id');
            $table->unsignedBigInteger('blockable_id')->after('blockable_type');
            $table->index(['blockable_type', 'blockable_id']);
            $table->index(['site_id', 'blockable_type', 'blockable_id', 'position']);
        });
    }
};
