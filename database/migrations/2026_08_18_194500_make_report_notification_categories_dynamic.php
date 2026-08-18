<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings_notifications_categories', function (Blueprint $table) {
            $table->string('section', 30)->nullable()->after('type')->index();
            $table->unsignedSmallInteger('sort_order')->default(0)->after('section');
        });

        $reportSlugs = [
            'site.jobstartexport',
            'site.upcoming.compliance',
            'site.extension',
            'site.maintenance.noaction',
            'site.maintenance.onhold',
            'site.maintenance.appointment',
            'site.maintenance.aftercare',
            'site.maintenance.underreview',
            'site.maintenance.super.noaction',
            'site.maintenance.executive',
            'site.asbestos.active',
            'site.qa.outstanding',
            'site.inspection.open',
            'site.inspection.pending',
            'site.attendance.super',
            'site.attendance.trades',
            'site.prac.complation.super.noaction',
            'site.projectsupply.overdue',
            'site.supervisor.export',
            'site.nowork.planned',
            'equipment.transfers',
            'equipment.restock',
            'company.missing.info',
            'company.doc.pending',
            'user.oldusers'
        ];

        foreach ($reportSlugs as $index => $slug) {
            DB::table('settings_notifications_categories')
                ->where('slug', $slug)
                ->update([
                    'section' => 'report',
                    'sort_order' => ($index + 1) * 10,
                ]);
        }

        $exists = DB::table('settings_notifications_categories')
            ->where('slug', 'site.foc.defective')
            ->exists();

        if (!$exists) {
            DB::table('settings_notifications_categories')->insert([
                'section' => 'report',
                'sort_order' => 260,
                'slug' => 'site.foc.defective',
                'name' => 'FOC Defective Inspections',
                'brief' => 'Cc recipients for the weekly outstanding FOC Defective report. The Site Supervisor is always the primary recipient.',
                'status' => 1,
                'company_id' => 3,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('settings_notifications_categories')
                ->where('slug', 'site.foc.defective')
                ->update([
                    'section' => 'report',
                    'sort_order' => 260,
                ]);
        }
    }

    public function down(): void
    {
        $categoryId = DB::table('settings_notifications_categories')
            ->where('slug', 'site.foc.defective')
            ->value('id');

        if ($categoryId) {
            DB::table('settings_notifications')
                ->where('type', $categoryId)
                ->delete();

            DB::table('settings_notifications_categories')
                ->where('id', $categoryId)
                ->delete();
        }

        Schema::table('settings_notifications_categories', function (Blueprint $table) {
            $table->dropColumn(['section', 'sort_order']);
        });
    }
};
