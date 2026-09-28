<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_contracts', function (Blueprint $table) {
            $table->string('owner1_firstname')->nullable()->after('owner1_name');
            $table->string('owner1_lastname')->nullable()->after('owner1_firstname');
            $table->string('owner2_firstname')->nullable()->after('owner2_name');
            $table->string('owner2_lastname')->nullable()->after('owner2_firstname');
        });
    }

    public function down(): void
    {
        Schema::table('site_contracts', function (Blueprint $table) {
            $table->dropColumn([
                'owner1_firstname',
                'owner1_lastname',
                'owner2_firstname',
                'owner2_lastname',
            ]);
        });
    }
};
