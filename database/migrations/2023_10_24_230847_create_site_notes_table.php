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
        Schema::create('site_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('price', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('site_notes_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->integer('parent')->unsigned()->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('site_notes');
        Schema::dropIfExists('site_notes_categories');
    }
};
