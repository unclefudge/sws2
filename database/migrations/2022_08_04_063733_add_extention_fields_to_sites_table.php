<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtentionFieldsToSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_extensions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('approved_by')->unsigned()->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('site_extensions_sites', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('extension_id')->unsigned()->nullable();
            $table->integer('site_id')->unsigned()->nullable();
            $table->dateTime('completion_date')->nullable();
            $table->string('reasons', 255)->nullable();
            $table->text('notes')->nullable();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });


        Schema::create('site_extensions_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->integer('parent')->unsigned()->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('company_id')->unsigned()->nullable();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companys')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();

            $table->timestamps();
        });

        Schema::table('sites', function (Blueprint $table) {

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('special');
        });

        Schema::dropIfExists('site_extensions_categories');
        Schema::dropIfExists('site_extensions_sites');
        Schema::dropIfExists('site_extensions');
    }
}
