<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserDocsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 10)->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('name', 100)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->dateTime('expiry')->nullable();
            $table->string('version', 10)->nullable();
            $table->string('ref_no', 50)->nullable();
            $table->string('ref_name', 100)->nullable();
            $table->string('ref_type', 100)->nullable();
            $table->tinyInteger('private')->default(0);
            $table->string('share', 2)->default('b');
            $table->text('notes')->nullable();
            $table->text('reject')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('user_id')->unsigned()->default(0);
            $table->integer('company_id')->unsigned()->default(0);
            $table->integer('approved_by')->unsigned();
            $table->dateTime('approved_at')->nullable();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companys');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_docs_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 10)->nullable();
            $table->string('name', 100)->nullable();
            $table->integer('parent')->unsigned();
            $table->tinyInteger('private')->default(0);
            $table->tinyInteger('notify')->default(0);
            $table->tinyInteger('multiple')->default(1);
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->integer('company_id')->unsigned()->default(0);
            $table->foreign('company_id')->references('id')->on('companys');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_docs');
        Schema::dropIfExists('user_docs_categories');
    }
}
