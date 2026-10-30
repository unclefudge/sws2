<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('da_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('planning_officer')->nullable();
            $table->string('cft_number')->nullable();
            $table->string('cc_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['da_number', 'pan_number', 'planning_officer', 'cft_number', 'cc_number']);
        });
    }
};
