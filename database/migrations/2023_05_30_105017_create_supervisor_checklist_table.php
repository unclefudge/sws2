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
        Schema::create('supervisor_checklist', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('super_id')->unsigned()->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('supervisor_sign_by')->unsigned()->nullable();
            $table->timestamp('supervisor_sign_at')->nullable();
            $table->integer('manager_sign_by')->unsigned()->nullable();
            $table->timestamp('manager_sign_at')->nullable();
            $table->integer('approved_by')->unsigned()->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        //
        // Checklist Categories
        //
        Schema::create('supervisor_checklist_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->integer('parent')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        //
        // Checklist Questions
        //
        Schema::create('supervisor_checklist_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cat_id')->unsigned()->nullable();
            $table->string('name', 255)->nullable();
            $table->string('type', 50)->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->string('default', 255)->nullable();
            $table->tinyInteger('multiple')->nullable();
            $table->tinyInteger('required')->nullable();
            $table->string('placeholder', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });


        //
        // Checklist Responses
        //
        Schema::create('supervisor_checklist_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('checklist_id')->unsigned();
            $table->integer('day')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->text('value')->nullable();
            $table->dateTime('date')->nullable();
            $table->tinyInteger('status')->default(1);

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Checklist Notes
        //
        Schema::create('supervisor_checklist_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('checklist_id')->unsigned();
            $table->text('info')->nullable();
            $table->string('attachment', 255)->nullable();
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
        Schema::dropIfExists('supervisor_checklist');
        Schema::dropIfExists('supervisor_checklist_categories');
        Schema::dropIfExists('supervisor_checklist_questions');
        Schema::dropIfExists('supervisor_checklist_responses');
        Schema::dropIfExists('supervisor_checklist_notes');
    }
};
