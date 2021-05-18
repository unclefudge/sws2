<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteAsbestosRegisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_asbestos_register', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned()->nullable();
            $table->string('version', 10)->nullable();
            $table->integer('approved_by')->unsigned()->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('site_asbestos_register_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('register_id')->unsigned()->nullable();
            $table->dateTime('date')->nullable();
            $table->string('type', 255)->nullable();
            $table->text('location')->nullable();
            $table->tinyInteger('friable');
            $table->string('amount', 10)->nullable();
            $table->text('condition')->nullable();
            $table->text('assessment')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreign('register_id')->references('id')->on('site_asbestos_register')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_asbestos_register_items');
        Schema::dropIfExists('site_asbestos_register');
    }
}
