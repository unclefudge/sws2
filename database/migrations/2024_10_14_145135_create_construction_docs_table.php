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
        Schema::create('construction_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 10)->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('name', 100)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->dateTime('expiry')->nullable();
            $table->string('reference', 255)->nullable();
            $table->string('version', 10)->nullable();
            $table->integer('approved_by')->unsigned();
            $table->dateTime('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('construction_docs');
    }
};
