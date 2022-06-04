<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDocsReviewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_docs_review', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('doc_id')->unsigned();
            //$table->integer('todo_id')->unsigned()->nullable();
            $table->string('name')->nullable();
            $table->tinyInteger('stage')->nullable();
            $table->dateTime('approved_con')->nullable();
            $table->dateTime('approved_eng')->nullable();
            $table->dateTime('approved_adm')->nullable();
            $table->string('original_doc', 255)->nullable();
            $table->string('current_doc', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(0);

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('company_docs_review_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('review_id')->unsigned();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(0);

            // Foreign keys
            $table->foreign('review_id')->references('id')->on('company_docs_review')->onDelete('cascade');

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
        Schema::dropIfExists('company_docs_review_files');
        Schema::dropIfExists('company_docs_review');
    }
}
