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
        Schema::create('supervisor_checklist_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('field', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('value')->nullable();
            $table->string('colour', 50)->nullable();
            $table->integer('order')->nullable();
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
        Schema::dropIfExists('supervisor_checklist_settings');
    }
};
