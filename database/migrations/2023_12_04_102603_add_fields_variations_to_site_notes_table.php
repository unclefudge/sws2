<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('site_notes', function (Blueprint $table) {
            $table->string('variation_name', 255)->nullable();
            $table->text('variation_info')->nullable();
            $table->string('variation_cost', 100)->nullable();
            $table->tinyInteger('variation_days')->nullable();
            $table->dropColumn('attachment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('site_notes', function (Blueprint $table) {
            $table->dropColumn('variation_days');
            $table->dropColumn('variation_cost');
            $table->dropColumn('variation_info');
            $table->dropColumn('variation_name');
        });
    }
};
