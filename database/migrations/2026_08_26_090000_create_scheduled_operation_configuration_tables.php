<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each table is checked separately so this migration can safely resume
        // if MySQL completed an earlier CREATE TABLE before a later index failed.
        if (!Schema::hasTable('scheduled_operation_definitions')) {
            Schema::create('scheduled_operation_definitions', function (Blueprint $table) {
                $table->id();
                $table->string('task_key')->unique('sched_op_def_task_key_uq');
                // handler_key resolves through the code whitelist. No PHP class or
                // method entered in the browser is ever executed directly.
                $table->string('handler_key')->index('sched_op_def_handler_idx');
                $table->string('name');
                $table->string('category', 40)->index('sched_op_def_category_idx');
                $table->text('description')->nullable();
                $table->text('recipient_summary')->nullable();
                $table->boolean('enabled')->default(true)->index('sched_op_def_enabled_idx');
                $table->string('schedule_type', 40);
                $table->json('schedule_data');
                $table->string('recipient_mode', 20)->default('legacy');
                $table->unsignedSmallInteger('tries')->default(3);
                $table->unsignedInteger('timeout_seconds')->default(240);
                $table->boolean('client_configurable')->default(false);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('scheduled_operation_recipient_rules')) {
            Schema::create('scheduled_operation_recipient_rules', function (Blueprint $table) {
                $table->id();
                // The explicit short index name avoids MySQL's 64-character limit.
                $table->unsignedBigInteger('scheduled_operation_definition_id')
                    ->index('sched_op_recipient_def_idx');
                $table->string('delivery_type', 10); // to, cc or bcc
                $table->string('source_type', 30);   // user, manual or notification_group
                $table->string('source_value')->nullable();
                $table->json('source_meta')->nullable();
                $table->string('label')->nullable();
                $table->boolean('enabled')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('scheduled_operation_definition_id', 'sched_op_recipient_def_fk')
                    ->references('id')->on('scheduled_operation_definitions')->cascadeOnDelete();
            });
        } else {
            // The first release could stop after creating this table but before
            // adding its index/foreign key. Complete those missing constraints.
            if (!$this->indexExists('scheduled_operation_recipient_rules', 'sched_op_recipient_def_idx')) {
                Schema::table('scheduled_operation_recipient_rules', function (Blueprint $table) {
                    $table->index('scheduled_operation_definition_id', 'sched_op_recipient_def_idx');
                });
            }

            if (!$this->foreignKeyExists('scheduled_operation_recipient_rules', 'sched_op_recipient_def_fk')) {
                Schema::table('scheduled_operation_recipient_rules', function (Blueprint $table) {
                    $table->foreign('scheduled_operation_definition_id', 'sched_op_recipient_def_fk')
                        ->references('id')->on('scheduled_operation_definitions')->cascadeOnDelete();
                });
            }
        }

        if (!Schema::hasTable('scheduled_operation_change_logs')) {
            Schema::create('scheduled_operation_change_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('scheduled_operation_definition_id')->nullable()
                    ->index('sched_op_change_def_idx');
                $table->unsignedBigInteger('user_id')->nullable()->index('sched_op_change_user_idx');
                $table->string('action', 40);
                $table->json('before')->nullable();
                $table->json('after')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_operation_change_logs');
        Schema::dropIfExists('scheduled_operation_recipient_rules');
        Schema::dropIfExists('scheduled_operation_definitions');
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = database()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return DB::table('information_schema.table_constraints')
            ->whereRaw('constraint_schema = database()')
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
