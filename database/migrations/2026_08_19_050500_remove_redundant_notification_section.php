<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('settings_notifications_categories', 'section')) {
            DB::table('settings_notifications_categories')
                ->where('section', 'report')
                ->update(['type' => 'report']);

            Schema::table('settings_notifications_categories', function (Blueprint $table) {
                $table->dropColumn('section');
            });
        }

        DB::table('settings_notifications_categories')
            ->where('slug', 'site.foc.defective')
            ->update([
                'type' => 'report',
                'title' => 'FOC Defective Inspections',
                'body' => "Each Supervisor receives a weekly report of their Jobs with outstanding FOC items in the Defective category. Users selected here are Cc'd on each Supervisor report.",
                'brief' => 'Report sent weekly (Monday)',
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('settings_notifications_categories', 'section')) {
            Schema::table('settings_notifications_categories', function (Blueprint $table) {
                $table->string('section', 30)->nullable()->after('type')->index();
            });

            DB::table('settings_notifications_categories')
                ->where('type', 'report')
                ->update(['section' => 'report']);
        }
    }
};
