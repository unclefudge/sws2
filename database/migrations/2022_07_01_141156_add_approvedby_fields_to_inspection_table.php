<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovedbyFieldsToInspectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_inspection_electrical', function (Blueprint $table) {
            $table->integer('supervisor_sign_by')->unsigned()->nullable();
            $table->timestamp('supervisor_sign_at')->nullable();
            $table->integer('manager_sign_by')->unsigned()->nullable();
            $table->timestamp('manager_sign_at')->nullable();
        });


        Schema::table('site_inspection_plumbing', function (Blueprint $table) {
            $table->integer('supervisor_sign_by')->unsigned()->nullable();
            $table->timestamp('supervisor_sign_at')->nullable();
            $table->integer('manager_sign_by')->unsigned()->nullable();
            $table->timestamp('manager_sign_at')->nullable();
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
