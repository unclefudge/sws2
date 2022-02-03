<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScaffoldHandoverTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_scaffold_handover', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('site_id')->unsigned()->nullable();
            $table->text('location')->nullable();
            $table->text('use')->nullable();
            $table->string('duty', 255)->nullable();
            $table->integer('decks')->nullable();

            $table->string('inspector_name', 255)->nullable();
            $table->string('inspector_licence', 255)->nullable();
            $table->dateTime('handover_date')->nullable();
            $table->integer('signed_by')->nullable();
            $table->dateTime('signed_at')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        //
        // Attachments
        //
        Schema::create('site_scaffold_handover_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('scaffold_id')->unsigned()->nullable();
            $table->string('type', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('scaffold_id')->references('id')->on('site_scaffold_handover')->onDelete('cascade');

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
        Schema::dropIfExists('site_scaffold_handover_docs');
        Schema::dropIfExists('site_scaffold_handover');
    }
}
