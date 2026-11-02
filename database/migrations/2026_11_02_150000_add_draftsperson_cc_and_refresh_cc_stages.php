<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('draftsperson_cc')->nullable();
        });

        if (Schema::hasTable('site_cc')) {
            // Reclassify existing records; do not create records or change their dates/statuses.
            DB::table('site_cc')->update(['stage' => DB::raw("CASE
                WHEN status = -1 OR EXISTS (SELECT 1 FROM sites WHERE sites.id = site_cc.site_id AND sites.status = -2) THEN 'Disabled'
                WHEN EXISTS (SELECT 1 FROM sites WHERE sites.id = site_cc.site_id AND sites.construction_rcvd IS NOT NULL) THEN 'CC Received'
                WHEN cc_requested IS NOT NULL THEN 'CC Requested'
                ELSE 'Upcoming' END")]);
        }
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('draftsperson_cc');
        });
        // Old application stage logic can reconcile stages after an application rollback.
    }
};
