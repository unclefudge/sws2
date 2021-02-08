<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedFieldsToSiteMaintenanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_maintenance', function (Blueprint $table) {
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('assigned_super_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_maintenance', function($table) {
            $table->dropColumn('assigned_super_at');
            $table->dropColumn('assigned_at');
        });
    }
}
