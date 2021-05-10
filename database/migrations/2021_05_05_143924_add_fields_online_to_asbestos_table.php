<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsOnlineToAsbestosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_asbestos', function (Blueprint $table) {
            $table->string('client_name', 255)->nullable();
            $table->string('client_phone', 50)->nullable();
            $table->integer('super_id')->unsigned()->nullable();
            $table->string('super_phone', 50)->nullable();
            $table->string('workplace', 255)->nullable();

            $table->tinyInteger('coalmine')->nullable();
            $table->tinyInteger('hygiene')->nullable();
            $table->string('hygiene_report', 50)->nullable();

            // Assessor
            $table->string('assessor_name', 255)->nullable();
            $table->string('assessor_phone', 50)->nullable();
            $table->string('assessor_cert', 50)->nullable();
            $table->string('assessor_lic', 25)->nullable();
            $table->string('assessor_dept', 50)->nullable();
            $table->string('assessor_state', 25)->nullable();

            // Lab
            $table->string('lab_nata', 255)->nullable();
            $table->string('lab_name', 255)->nullable();

            // SafeWork
            $table->tinyInteger('safework')->nullable();
            $table->string('safework_ref', 25)->nullable();

            // Date of actions
            $table->dateTime('safework_at')->nullable();
            $table->dateTime('supervisor_at')->nullable();
            $table->dateTime('neighbours_at')->nullable();
            $table->dateTime('removal_at')->nullable();
            $table->dateTime('reg_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_asbestos', function($table) {
            $table->dropColumn('reg_updated_at');
            $table->dropColumn('removal_at');
            $table->dropColumn('neighbours_at');
            $table->dropColumn('supervisor_at');
            $table->dropColumn('safework_at');
            $table->dropColumn('safework_ref');
            $table->dropColumn('safework');
            $table->dropColumn('lab_name');
            $table->dropColumn('lab_nata');
            $table->dropColumn('assessor_state');
            $table->dropColumn('assessor_dept');
            $table->dropColumn('assessor_lic');
            $table->dropColumn('assessor_cert');
            $table->dropColumn('assessor_phone');
            $table->dropColumn('assessor_name');
            $table->dropColumn('hygiene_report');
            $table->dropColumn('hygiene');
            $table->dropColumn('coalmine');
            $table->dropColumn('workplace');
            $table->dropColumn('super_phone');
            $table->dropColumn('super_id');
            $table->dropColumn('client_phone');
            $table->dropColumn('client_name');
        });
    }
}
