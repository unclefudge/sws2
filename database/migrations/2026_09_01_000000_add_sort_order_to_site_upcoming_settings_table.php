<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_upcoming_settings', function (Blueprint $table): void {
            $table->integer('sort_order')->nullable()->after('order');
        });

        DB::table('site_upcoming_settings')
            ->whereIn('field', ['opt', 'cfest', 'cfadm'])
            ->whereNotNull('order')
            ->orderBy('id')
            ->chunkById(200, function ($settings): void {
                foreach ($settings as $setting) {
                    DB::table('site_upcoming_settings')
                        ->where('id', $setting->id)
                        ->update(['sort_order' => ((int) $setting->order) * 10]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('site_upcoming_settings', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
    }
};
