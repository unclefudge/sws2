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
        Schema::table('site_notes', function (Blueprint $table) {
            $table->dateTime('occupation_date')->nullable();
            $table->text('occupation_area')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_notes', function (Blueprint $table) {
            $table->dropColumn('occupation_area');
            $table->dropColumn('occupation_date');
        });
    }
};
