<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('scheduled_operation_definitions')) {
            return;
        }

        // These keys all have dedicated ScheduledOperationHandler classes.
        // No-email handlers are included so their obsolete legacy state is
        // removed even though recipient routing is bypassed for them.
        DB::table('scheduled_operation_definitions')
            ->whereIn('task_key', [
                'hourly.client_enquiry_followup',
                'hourly.super_checklist_reminder',
                'hourly.sync_foc_stages',
                'nightly.asbestos_notifications',
                'nightly.archive_toolbox',
                'nightly.blessing',
                'nightly.broken_qa_items',
                'nightly.expired_company_docs',
                'nightly.expired_standard_details',
                'nightly.expired_swms',
                'nightly.extension_task',
                'nightly.non_attendees',
                'nightly.overdue_todos',
                'nightly.planner_key_actions',
                'nightly.qa',
                'nightly.rogue_todos',
                'nightly.roster',
                'nightly.site_extensions',
                'nightly.super_checklists',
            ])
            ->update([
                'recipient_mode' => 'managed',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Recipient modes before this migration differed by installation and
        // cannot be reconstructed safely. The compatibility column remains.
    }
};
