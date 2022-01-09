<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSafetyDataSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('safety_sds_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 10)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('manufacturer', 255)->nullable();
            $table->text('application')->nullable();
            $table->tinyInteger('hazardous')->nullable();
            $table->tinyInteger('dangerous')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->dateTime('date')->nullable();
            $table->dateTime('expiry')->nullable();
            $table->string('reference', 50)->nullable();
            $table->string('version', 10)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('company_id')->unsigned()->default(0);

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companys')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        //
        // Responses to questions
        //
        Schema::create('safety_sds_cats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sds_id')->unsigned();
            $table->integer('cat_id')->unsigned();

            // Foreign keys
            $table->foreign('sds_id')->references('id')->on('safety_sds_docs')->onDelete('cascade');
            $table->foreign('cat_id')->references('id')->on('safety_docs_categories')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('safety_sds_cats');
        Schema::dropIfExists('safety_sds_docs');
    }
}
