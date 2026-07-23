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
        Schema::table('sites', function (Blueprint $table) {
            $table->text('damage_deposit')->nullable();
            $table->dateTime('cp_sent_client')->nullable();
            $table->dateTime('oc_rcvd_date')->nullable();
        });

        Schema::table('site_foc', function (Blueprint $table) {
            $table->dateTime('foc_requested')->nullable();
            $table->boolean('portal_fee_paid')->nullable();
            $table->boolean('wbo_waiting')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_foc', function (Blueprint $table) {
            $table->dropColumn([
                'foc_requiested',
                'portal_fee_paid',
                'wbo_waiting',
            ]);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'damage_deposit',
                'cp_sent_client',
                'oc_rcvd_date',
            ]);
        });
    }
};
