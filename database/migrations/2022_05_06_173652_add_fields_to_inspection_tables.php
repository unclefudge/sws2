<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToInspectionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_inspection_electrical', function (Blueprint $table) {
            $table->text('trade_notes')->nullable();
        });


        Schema::table('site_inspection_plumbing', function (Blueprint $table) {
            $table->text('trade_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_inspection_electrical', function($table) {
            $table->dropColumn('trade_notes');
        });

        Schema::table('site_inspection_plumbing', function($table) {
            $table->dropColumn('trade_notes');
        });
    }
}
