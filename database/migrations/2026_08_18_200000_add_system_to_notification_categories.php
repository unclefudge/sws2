<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings_notifications_categories', function (Blueprint $table) {
            $table->boolean('system')->default(1)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('settings_notifications_categories', function (Blueprint $table) {
            $table->dropColumn('system');
        });
    }
};
