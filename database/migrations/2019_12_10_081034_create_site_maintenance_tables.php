<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteMaintenanceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // Site Maintenance
        //
        Schema::create('site_maintenance', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned()->nullable();
            $table->dateTime('completed')->nullable();
            $table->integer('super_id')->unsigned()->nullable();
            $table->string('warranty', 100)->nullable();
            $table->tinyInteger('goodwill')->unsigned()->default(0);
            $table->tinyInteger('category_id')->unsigned()->nullable();
            $table->integer('supervisor_sign_by')->unsigned()->nullable();
            $table->timestamp('supervisor_sign_at')->nullable();
            $table->integer('manager_sign_by')->unsigned()->nullable();
            $table->timestamp('manager_sign_at')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('site_maintenance_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('main_id')->unsigned()->nullable();
            $table->text('name')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->tinyInteger('super')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->integer('sign_by')->unsigned()->nullable();
            $table->timestamp('sign_at')->nullable();
            $table->integer('done_by')->unsigned()->nullable();

            // Foreign keys
            $table->foreign('main_id')->references('id')->on('site_maintenance')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });


        //
        // Attachments
        //
        Schema::create('site_maintenance_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('main_id')->unsigned()->nullable();
            $table->string('type', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('main_id')->references('id')->on('site_maintenance')->onDelete('cascade');

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
        Schema::dropIfExists('site_maintenance_attachment');
        Schema::dropIfExists('site_maintenance_items');
        Schema::dropIfExists('site_maintenance');
    }
}
