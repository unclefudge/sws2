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
        Schema::create('zoho_sitelog', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('user_name')->nullable();
            $table->string('action', 25);
            $table->integer('qty')->unsigned()->nullable();
            $table->text('fields')->nullable();
            $table->text('old')->nullable();
            $table->text('new')->nullable();
            $table->text('log')->nullable();

            // Foreign keys
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');

            // Modify info
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoho_sitelog');
    }
};
