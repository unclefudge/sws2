<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('scheduled_operation_definitions')
            && Schema::hasColumn('scheduled_operation_definitions', 'recipient_mode')) {
            Schema::table('scheduled_operation_definitions', function (Blueprint $table) {
                $table->dropColumn('recipient_mode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('scheduled_operation_definitions')
            && !Schema::hasColumn('scheduled_operation_definitions', 'recipient_mode')) {
            Schema::table('scheduled_operation_definitions', function (Blueprint $table) {
                $table->string('recipient_mode', 20)->default('managed')->after('schedule_data');
            });
        }
    }
};
