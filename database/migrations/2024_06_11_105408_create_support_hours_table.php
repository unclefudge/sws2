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
        Schema::create('support_hours', function (Blueprint $table) {
            $table->id();
            $table->string('day')->nullable();
            $table->tinyInteger('h9_11')->default(0);
            $table->tinyInteger('h11_1')->default(0);
            $table->tinyInteger('h1_3')->default(0);
            $table->tinyInteger('h3_5')->default(0);
            $table->integer('order')->nullable();;
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_hours');
    }
};
