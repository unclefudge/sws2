<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsAssignedAtToInspectionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_inspection_plumbing', function (Blueprint $table) {
            $table->dateTime('assigned_at')->nullable();
        });

        Schema::table('site_inspection_electrical', function (Blueprint $table) {
            $table->dateTime('assigned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_inspection_plumbing', function($table) {
            $table->dropColumn('assigned_at');
        });

        Schema::table('site_inspection_electrical', function($table) {
            $table->dropColumn('assigned_at');
        });
    }
}
