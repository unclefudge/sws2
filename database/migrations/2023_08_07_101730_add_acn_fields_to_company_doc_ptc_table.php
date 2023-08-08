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
        Schema::table('company_doc_ptc', function (Blueprint $table) {
            $table->string('principle_acn', '20')->nullable();
            $table->string('contractor_acn', '20')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_doc_ptc', function (Blueprint $table) {
            $table->dropColumn('contractor_acn');
            $table->dropColumn('principle_acn');
        });
    }
};
