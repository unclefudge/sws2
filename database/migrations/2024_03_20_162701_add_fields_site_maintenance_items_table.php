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
        Schema::table('site_maintenance_items', function (Blueprint $table) {
            $table->integer('assigned_to')->unsigned()->nullable();
            $table->integer('planner_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_maintenance_items', function (Blueprint $table) {
            $table->dropColumn('planner_id');
            $table->dropColumn('assigned_to');
        });
    }
};
