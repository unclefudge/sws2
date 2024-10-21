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
            $table->dateTime('prac_notified')->nullable();
            $table->dateTime('prac_meeting')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_notes', function (Blueprint $table) {
            $table->dropColumn('prac_meeting');
            $table->dropColumn('prac_notified');
        });
    }
};
