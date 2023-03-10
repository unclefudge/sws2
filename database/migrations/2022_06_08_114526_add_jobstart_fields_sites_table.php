<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobstartFieldsSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('cc')->nullable();
            $table->tinyInteger('cc_stage')->nullable();
            $table->string('fc_plans')->nullable();
            $table->tinyInteger('fc_plans_stage')->nullable();
            $table->string('fc_struct')->nullable();
            $table->tinyInteger('fc_struct_stage')->nullable();
            $table->string('cf_est')->nullable();
            $table->tinyInteger('cf_est_stage')->nullable();
            $table->string('cf_adm')->nullable();
            $table->tinyInteger('cf_adm_stage')->nullable();
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
            $table->dropColumn('cf_adm_stage');
            $table->dropColumn('cf_adm');
            $table->dropColumn('cf_est_stage');
            $table->dropColumn('cf_est');
            $table->dropColumn('fc_struct_stage');
            $table->dropColumn('fc_struct');
            $table->dropColumn('fc_plans_stage');
            $table->dropColumn('fc_plans');
            $table->dropColumn('cc_stage');
            $table->dropColumn('cc');
        });
    }
}
