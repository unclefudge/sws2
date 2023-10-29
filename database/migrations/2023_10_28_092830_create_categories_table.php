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
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 255)->nullable();
            $table->string('sub_type', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('value', 255)->nullable();
            $table->string('brief', 255)->nullable();
            $table->string('colour', 255)->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->integer('parent')->unsigned()->nullable();
            $table->tinyInteger('multiple')->nullable();
            $table->tinyInteger('notify')->nullable();
            $table->text('notes')->nullable();
            $table->integer('company_id')->unsigned()->nullable();
            $table->tinyInteger('status')->default(1);

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
        Schema::dropIfExists('categories');
    }
};
