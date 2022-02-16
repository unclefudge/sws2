<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteClientEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_planner_emails', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('client_id')->unsigned()->nullable();
            $table->integer('site_id')->unsigned()->nullable();
            $table->string('type', 50)->nullable();
            $table->string('name', 255)->nullable();

            // Client
            $table->string('email1', 255)->nullable();
            $table->string('email2', 255)->nullable();
            $table->string('intro', 255)->nullable();

            // Email
            $table->string('sent_to', 255)->nullable();
            $table->string('sent_cc', 255)->nullable();
            $table->string('sent_bcc', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->text('body')->nullable();

            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Attachments
        //
        Schema::create('client_planner_emails_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('email_id')->unsigned()->nullable();
            $table->string('attachment', 255)->nullable();

            // Foreign keys
            $table->foreign('email_id')->references('id')->on('client_planner_emails')->onDelete('cascade');

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
        Schema::dropIfExists('client_planner_emails_docs');
        Schema::dropIfExists('client_planner_emails');

    }
}
