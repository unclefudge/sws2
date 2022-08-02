<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignoffFieldsToProjectSupplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_supply', function (Blueprint $table) {
            $table->integer('supervisor_sign_by')->unsigned()->nullable();
            $table->timestamp('supervisor_sign_at')->nullable();
            $table->integer('manager_sign_by')->unsigned();
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
        Schema::table('project_supply', function (Blueprint $table) {
            $table->dropColumn('manager_sign_at');
            $table->dropColumn('manager_sign_by');
            $table->dropColumn('supervisor_sign_at');
            $table->dropColumn('supervisor_sign_by');
        });
    }
}
