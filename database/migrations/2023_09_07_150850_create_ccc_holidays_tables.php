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
        Schema::create('zccc_youth', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name', 100)->nullable();
            $table->dateTime('dob')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('parent', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('pickup', 100)->nullable();
            $table->text('medical')->nullable();
            $table->string('consent_photo', 10)->nullable();
            $table->string('consent_movie', 10)->nullable();
            $table->string('consent_medicine', 10)->nullable();
            $table->string('leave_unsupervised', 10)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('zccc_programs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('cost')->nullable();
            $table->integer('max')->nullable();
            $table->string('pickups')->nullable();
            $table->string('brief', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('zccc_pickups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('zccc_programs_youth', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('youth_id')->nullable();
            $table->integer('program_id')->nullable();
            $table->tinyInteger('status')->nullable();
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
        Schema::dropIfExists('zccc_programs_youth');
        Schema::dropIfExists('zccc_pickups');
        Schema::dropIfExists('zccc_programs');
        Schema::dropIfExists('zccc_youth');
    }
};
