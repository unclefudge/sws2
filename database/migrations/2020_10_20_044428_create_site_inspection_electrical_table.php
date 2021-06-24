<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteInspectionElectricalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_inspection_electrical', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned()->nullable();

            // Client
            $table->string('client_name', 150)->nullable();
            $table->string('client_address', 255)->nullable();
            $table->dateTime('client_contacted')->nullable();

            // Inspection
            $table->integer('assigned_to')->unsigned()->nullable();
            $table->integer('inspected_by')->unsigned()->nullable();
            $table->dateTime('inspected_at')->nullable();
            $table->string('inspected_name', 100)->nullable();
            $table->string('inspected_lic', 20)->nullable();

            // Works
            $table->text('existing')->nullable();
            $table->text('required')->nullable();
            $table->string('required_cost', 10)->nullable();
            $table->text('recommend')->nullable();
            $table->string('recommend_cost', 10)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(2);

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
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
        Schema::dropIfExists('site_inspection_electrical');
    }
}
