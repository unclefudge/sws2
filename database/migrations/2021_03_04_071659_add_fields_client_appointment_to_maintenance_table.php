<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsClientAppointmentToMaintenanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_maintenance', function (Blueprint $table) {
            $table->dateTime('client_contacted_')->nullable();
            $table->dateTime('client_appointment')->nullable();
            $table->dateTime('ac_form_sent')->nullable();
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
            $table->dropColumn('ac_form_sent');
            $table->dropColumn('client_appointment');
            $table->dropColumn('client_contacted');
        });
    }
}
