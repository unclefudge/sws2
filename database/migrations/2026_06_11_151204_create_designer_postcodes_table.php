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
        Schema::create('designer_postcodes', function (Blueprint $table) {
            $table->id();
            $table->string('postcode', 10);
            $table->string('suburb')->nullable();
            $table->string('state', 10)->default('NSW');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['postcode', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designer_postcodes');
    }
};
