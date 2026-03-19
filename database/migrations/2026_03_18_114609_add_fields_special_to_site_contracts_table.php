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
        Schema::table('site_contracts', function (Blueprint $table) {
            $table->string('warranty_amount')->nullable();
            $table->string('special_conditions')->nullable();
            $table->text('special_conditions_full')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_contracts', function (Blueprint $table) {
            $table->dropColumn('special_conditions_full');
            $table->dropColumn('special_conditions');
            $table->dropColumn('warranty_amount');
        });
    }
};
