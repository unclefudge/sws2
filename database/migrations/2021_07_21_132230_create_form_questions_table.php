<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // Form questions 'dropdown options'
        //
        Schema::create('site_incidents_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('label', 255)->nullable();
            $table->integer('parent')->unsigned()->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->string('form', 255)->nullable();
            $table->integer('company_id')->unsigned()->nullable();
            $table->tinyInteger('status')->default(1);

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Responses to questions
        //
        Schema::create('site_incidents_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id')->unsigned();
            $table->integer('option_id')->unsigned();
            $table->string('table', 255)->nullable();
            $table->integer('table_id')->unsigned()->nullable();
            $table->text('info')->nullable();

            // Foreign keys
            $table->foreign('question_id')->references('id')->on('site_incidents_questions')->onDelete('cascade');

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
        Schema::dropIfExists('site_incidents_responses');
        Schema::dropIfExists('site_incidents_questions');
    }
}
