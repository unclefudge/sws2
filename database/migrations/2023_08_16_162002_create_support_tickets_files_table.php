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
        Schema::create('support_tickets_actions_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('action_id')->unsigned();
            $table->string('type', 10)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreign('action_id')->references('id')->on('support_tickets_actions')->onDelete('cascade');

            // Modify info
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
        Schema::dropIfExists('support_tickets_actions_files');
    }
};
