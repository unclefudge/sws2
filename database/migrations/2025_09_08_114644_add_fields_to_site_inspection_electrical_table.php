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
            $table->string('ausgrid', 10)->nullable();
            $table->string('clientbill', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_inspection_electrical', function (Blueprint $table) {
            $table->dropColumn('clientbill');
            $table->dropColumn('ausgrid');
        });
    }
};
