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
        Schema::create('site_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id')->unsigned()->nullable();
            $table->string('owner1_title')->nullable();
            $table->string('owner1_name')->nullable();
            $table->string('owner1_mobile')->nullable();
            $table->string('owner1_email')->nullable();
            $table->string('owner1_abn')->nullable();
            $table->string('owner2_title')->nullable();
            $table->string('owner2_name')->nullable();
            $table->string('owner2_mobile')->nullable();
            $table->string('owner2_email')->nullable();
            $table->string('owner2_abn')->nullable();
            $table->string('owner_address')->nullable();
            $table->string('owner_suburb')->nullable();
            $table->string('owner_state')->nullable();
            $table->string('owner_postcode')->nullable();
            $table->string('contract_price')->nullable();
            $table->string('contract_net')->nullable();
            $table->string('contract_gst')->nullable();
            $table->string('deposit')->nullable();
            $table->string('land_lot')->nullable();
            $table->string('land_dp')->nullable();
            $table->string('land_title')->nullable();
            $table->string('land_address')->nullable();
            $table->string('land_suburb')->nullable();
            $table->string('land_state')->nullable();
            $table->string('land_postcode')->nullable();
            $table->string('building_period')->nullable();
            $table->string('initial_period')->nullable();
            $table->string('hia_contract_id')->nullable();
            $table->string('hia_template_id')->nullable();
            $table->string('hia_pdf')->nullable();
            $table->longText('hia_xml')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->text('notes')->nullable();

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
        Schema::dropIfExists('site_contracts');
    }
};
