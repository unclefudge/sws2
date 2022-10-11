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
        //
        // Form Template
        //
        Schema::create('forms_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('company_id')->unsigned()->nullable();

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Form Pages
        //
        Schema::create('forms_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            //$table->foreign('template_id')->references('id')->on('forms_templates')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Form Sections
        //
        Schema::create('forms_sections', function (Blueprint $table) {
            $table->increments('id');
            //$table->integer('template_id')->unsigned();
            $table->integer('page_id')->unsigned();
            $table->integer('parent')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            //$table->foreign('page_id')->references('id')->on('forms_pages')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });


        //
        // Form Questions
        //
        Schema::create('forms_questions', function (Blueprint $table) {
            $table->increments('id');
            //$table->integer('template_id')->unsigned();
            //$table->integer('page_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->string('name', 255)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('type_special', 50)->nullable();
            $table->string('type_version', 50)->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->string('default', 255)->nullable();
            $table->tinyInteger('multiple')->nullable();
            $table->tinyInteger('required')->nullable();
            $table->string('placeholder', 255)->nullable();
            $table->text('helper')->nullable();
            $table->string('width', 25)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            //$table->foreign('section_id')->references('id')->on('forms_sections')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });


        //
        // Form Options
        //
        Schema::create('forms_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id')->unsigned();
            $table->string('text', 255)->nullable();
            $table->string('value', 255)->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->string('colour', 25)->nullable();
            $table->integer('score')->nullable();
            $table->string('group', 255)->nullable();
            $table->tinyInteger('master')->default(1);
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            //$table->foreign('question_id')->references('id')->on('forms_questions')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });


        //
        // Form Logic
        //
        Schema::create('forms_logic', function (Blueprint $table) {
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

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });


        //
        // Form
        //
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id')->unsigned();
            $table->integer('site_id')->unsigned()->nullable();
            $table->string('name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('company_id')->unsigned()->nullable();

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Form Responses
        //
        Schema::create('forms_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->integer('option_id')->unsigned()->nullable();
            $table->text('value')->nullable();
            $table->tinyInteger('status')->default(1);

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Form Files
        //
        Schema::create('forms_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->string('type', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->integer('order')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            //$table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Form Actions
        //
        Schema::create('forms_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->integer('todo_id')->unsigned();
            $table->text('action')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            //$table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');

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
        Schema::dropIfExists('forms_actions');
        Schema::dropIfExists('forms_files');
        Schema::dropIfExists('forms_responses');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('forms_logic');
        Schema::dropIfExists('forms_options');
        Schema::dropIfExists('forms_questions');
        Schema::dropIfExists('forms_sections');
        Schema::dropIfExists('forms_pages');
        Schema::dropIfExists('forms_templates');
    }
}
