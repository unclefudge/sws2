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
            $table->string('costing_priority')->nullable();
            $table->integer('parent')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_notes', function (Blueprint $table) {
            $table->dropColumn('parent');
            $table->dropColumn('costing_priority');
        });
    }
};
