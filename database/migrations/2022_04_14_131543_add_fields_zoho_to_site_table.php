<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsZohoToSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('client_intro', 255)->nullable();
            $table->dateTime('engineering_cert')->nullable();
            $table->dateTime('construction_rcvd')->nullable();
            $table->dateTime('hbcf_start')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('hbcf_start');
            $table->dropColumn('construction_rcvd');
            $table->dropColumn('engineering_cert');
            $table->dropColumn('client_intro');
        });
    }
}
