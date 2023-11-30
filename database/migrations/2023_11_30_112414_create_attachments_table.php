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
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table', 255)->nullable();
            $table->integer('table_id')->unsigned();
            $table->string('type', 25)->nullable();  // file, image
            $table->string('category', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->string('directory', 255)->nullable();
            $table->text('file')->nullable();
            $table->integer('order')->unsigned();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

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
        Schema::dropIfExists('attachments');
    }
};
