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
        Schema::table('site_inspection_electrical', function (Blueprint $table) {
            $table->string('nonausgrid')->nullable();
            $table->string('nonausgrid_weeks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_inspection_electrical', function (Blueprint $table) {
            $table->dropColumn('nonausgrid_weeks');
            $table->dropColumn('nonausgrid');
        });
    }
};
