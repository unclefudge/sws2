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
        Schema::table('site_notes', function (Blueprint $table) {
            $table->string('variation_net')->nullable();
        });

        // Variation Cost center items
        Schema::create('site_notes_costs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('note_id')->unsigned();
            $table->integer('cost_id')->unsigned();
            $table->string('details')->nullable();
            $table->text('notes')->nullable();

            // Foreign keys
            $table->foreign('note_id')->references('id')->on('site_notes')->onDelete('cascade');

            // Modify info
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_notes', function (Blueprint $table) {
            $table->dropColumn('variation_net');
        });

        Schema::dropIfExists('site_notes_costs');
    }
};
