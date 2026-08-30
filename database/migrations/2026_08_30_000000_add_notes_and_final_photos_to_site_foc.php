<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_foc_items', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('name');
        });

        Schema::table('site_foc', function (Blueprint $table) {
            $table->date('final_photos_rcvd')->nullable()->after('foc_requested');
        });
    }

    public function down(): void
    {
        Schema::table('site_foc_items', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('site_foc', function (Blueprint $table) {
            $table->dropColumn('final_photos_rcvd');
        });
    }
};
