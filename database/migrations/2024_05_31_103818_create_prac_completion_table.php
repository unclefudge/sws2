<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        //
        // Prac Completion
        //
        Schema::create('site_prac_completion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned()->nullable();
            $table->integer('super_id')->unsigned()->nullable();
            $table->dateTime('client_contacted')->nullable();
            $table->dateTime('client_appointment')->nullable();
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

        Schema::create('site_prac_completion_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('prac_id')->unsigned()->nullable();
            $table->text('name')->nullable();
            $table->integer('order')->unsigned()->nullable();
            $table->integer('assigned_to')->unsigned()->nullable();
            $table->integer('planner_id')->unsigned()->nullable();
            $table->integer('sign_by')->unsigned()->nullable();
            $table->timestamp('sign_at')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('prac_id')->references('id')->on('site_prac_completion')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_prac_completion_items');
        Schema::dropIfExists('site_prac_completion');

    }
};