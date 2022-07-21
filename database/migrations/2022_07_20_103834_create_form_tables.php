<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* Template */
        Schema::create('form_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('master')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('company_id')->unsigned()->nullable();

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Template - Page */
        Schema::create('form_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('order')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Template - Section */
        Schema::create('form_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->integer('page_id')->unsigned();
            $table->integer('parent')->unsigned()->nullable();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('order')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);


            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });


        /* Template - Question */
        Schema::create('form_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->integer('page_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('type_special', 50)->nullable();
            $table->string('type_version', 50)->nullable();
            $table->tinyInteger('order')->unsigned()->nullable();
            $table->string('default', 255)->nullable();
            $table->tinyInteger('multiple')->nullable();
            $table->tinyInteger('required')->nullable();
            $table->string('placeholder', 255)->nullable();
            $table->text('helper')->nullable();
            $table->tinyInteger('width')->default(100);
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Template - Question Option */
        Schema::create('form_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id')->unsigned()->nullable();
            $table->string('text', 255)->nullable();
            $table->string('value', 255)->nullable();
            $table->tinyInteger('order')->unsigned()->nullable();
            $table->string('colour', 25)->nullable();
            $table->integer('score')->nullable();
            $table->string('group', 255)->nullable();
            $table->tinyInteger('master')->nullable();
            $table->tinyInteger('status')->default(1);

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Template - Logic */
        Schema::create('form_logic', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->integer('page_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->string('match_operation', 25)->nullable();
            $table->string('match_value', 255)->nullable();
            $table->string('trigger', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            //$table->foreign('template_id')->references('id')->on('form_templates')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Form */
        Schema::create('form', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('company_id')->unsigned()->nullable();

            // Foreign keys
            //$table->foreign('template_id')->references('id')->on('form_templates')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Form - Responses*/
        Schema::create('form_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->integer('question_id')->unsigned()->nullable();
            $table->integer('option_id')->unsigned()->nullable();
            $table->text('value')->nullable();

            // Foreign keys
            //$table->foreign('form_id')->references('id')->on('form')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Form - File */
        Schema::create('form_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->string('type', 255)->nullable();
            $table->string('category', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->tinyInteger('order')->unsigned()->nullable();
            $table->text('notes')->nullable();

            // Foreign keys
            //$table->foreign('form_id')->references('id')->on('form')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        /* Form - Actions */
        Schema::create('form_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->integer('question_id')->unsigned()->nullable();
            $table->integer('todo_id')->unsigned()->nullable();
            $table->text('action')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();

            // Foreign keys
            //$table->foreign('form_id')->references('id')->on('form')->onDelete('cascade');

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
        Schema::dropIfExists('form_actions');
        Schema::dropIfExists('form_files');
        Schema::dropIfExists('form_responses');
        Schema::dropIfExists('form');
        Schema::dropIfExists('form_logic');
        Schema::dropIfExists('form_options');
        Schema::dropIfExists('form_questions');
        Schema::dropIfExists('form_sections');
        Schema::dropIfExists('form_pages');
        Schema::dropIfExists('form_templates');
    }
}
