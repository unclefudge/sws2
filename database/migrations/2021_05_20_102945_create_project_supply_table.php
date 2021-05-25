<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectSupplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_supply', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned()->nullable();
            $table->string('version', 10)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->integer('approved_by')->unsigned()->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('project_supply_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order')->nullable();
            $table->string('name', 100)->nullable();
            $table->text('supplier')->nullable();
            $table->text('type')->nullable();
            $table->text('colour')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        Schema::create('project_supply_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supply_id')->unsigned()->nullable();
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('product', 100)->nullable();
            $table->string('supplier', 255)->nullable();
            $table->string('type', 255)->nullable();
            $table->string('colour', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreign('supply_id')->references('id')->on('project_supply')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('project_supply_products')->onDelete('cascade');

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
        Schema::dropIfExists('project_supply_items');
        Schema::dropIfExists('project_supply_products');
        Schema::dropIfExists('project_supply');
    }
}
