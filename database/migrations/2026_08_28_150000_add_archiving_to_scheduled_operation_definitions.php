<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('scheduled_operation_definitions', 'archived_at')) {
            Schema::table('scheduled_operation_definitions', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('client_configurable')->index('sched_op_def_archived_idx');
            });
        }

        if (!Schema::hasColumn('scheduled_operation_definitions', 'archived_by')) {
            Schema::table('scheduled_operation_definitions', function (Blueprint $table) {
                $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at')->index('sched_op_def_archived_by_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('scheduled_operation_definitions', 'archived_by')) {
            Schema::table('scheduled_operation_definitions', function (Blueprint $table) {
                $table->dropIndex('sched_op_def_archived_by_idx');
                $table->dropColumn('archived_by');
            });
        }

        if (Schema::hasColumn('scheduled_operation_definitions', 'archived_at')) {
            Schema::table('scheduled_operation_definitions', function (Blueprint $table) {
                $table->dropIndex('sched_op_def_archived_idx');
                $table->dropColumn('archived_at');
            });
        }
    }
};
