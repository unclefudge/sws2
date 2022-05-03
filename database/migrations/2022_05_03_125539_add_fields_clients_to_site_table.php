<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsClientsToSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('client1_title', 25)->nullable();
            $table->string('client1_firstname', 100)->nullable();
            $table->string('client1_lastname', 100)->nullable();
            $table->string('client1_mobile', 50)->nullable();
            $table->string('client1_email', 255)->nullable();
            $table->string('client2_title', 25)->nullable();
            $table->string('client2_firstname', 100)->nullable();
            $table->string('client2_lastname', 100)->nullable();
            $table->string('client2_mobile', 50)->nullable();
            $table->string('client2_email', 255)->nullable();
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
            $table->dropColumn('client2_email');
            $table->dropColumn('client2_mobile');
            $table->dropColumn('client2_lastname');
            $table->dropColumn('client2_firstname');
            $table->dropColumn('client2_title');
            $table->dropColumn('client1_email');
            $table->dropColumn('client1_mobile');
            $table->dropColumn('client1_lastname');
            $table->dropColumn('client1_firstname');
            $table->dropColumn('client1_title');
        });
    }
}
