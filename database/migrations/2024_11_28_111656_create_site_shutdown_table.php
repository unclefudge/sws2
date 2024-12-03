<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('site_shutdown', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned()->nullable();
            $table->integer('super_id')->unsigned()->nullable();
            $table->string('version', 10)->nullable();
            $table->string('attachment', 255)->nullable();
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

        Schema::create('site_shutdown_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shutdown_id')->unsigned()->nullable();
            $table->string('category', 255)->nullable();
            $table->string('sub_category', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->string('type', 255)->nullable();
            $table->text('response')->nullable();

            // Foreign keys
            $table->foreign('shutdown_id')->references('id')->on('site_shutdown')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_shutdown_items');
        Schema::dropIfExists('site_shutdown');
    }
};
