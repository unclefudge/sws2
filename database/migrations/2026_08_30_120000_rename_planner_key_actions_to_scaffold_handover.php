<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_KEY = 'nightly.planner_key_actions';
    private const NEW_KEY = 'nightly.scaffold_handover_create';
    private const OLD_NAME = 'Create planner key actions';
    private const NEW_NAME = 'Create scaffold handovers';

    public function up(): void
    {
        DB::transaction(function () {
            $this->ensureTargetKeyIsAvailable(self::OLD_KEY, self::NEW_KEY);

            if (Schema::hasTable('scheduled_operation_definitions')) {
                DB::table('scheduled_operation_definitions')
                    ->where('task_key', self::OLD_KEY)
                    ->update([
                        'task_key' => self::NEW_KEY,
                        'handler_key' => self::NEW_KEY,
                        'name' => self::NEW_NAME,
                        'description' => 'Creates a Scaffold Handover Certificate and assigned review ToDo when a Scaffold Up planner task begins.',
                        'recipient_summary' => 'Assigned scaffold reviewer, affected Site Supervisor and the site.scaffold.handover.created notification group',
                        'updated_at' => now(),
                    ]);
            }

            if (Schema::hasTable('scheduled_task_settings')) {
                DB::table('scheduled_task_settings')
                    ->where('task_key', self::OLD_KEY)
                    ->update(['task_key' => self::NEW_KEY, 'updated_at' => now()]);
            }

            if (Schema::hasTable('scheduled_runs')) {
                DB::table('scheduled_runs')
                    ->where('task_key', self::OLD_KEY)
                    ->update(['task_key' => self::NEW_KEY, 'task_name' => self::NEW_NAME]);

                DB::table('scheduled_runs')
                    ->where('lock_key', 'like', '%:' . self::OLD_KEY . ':%')
                    ->update([
                        'lock_key' => DB::raw("REPLACE(lock_key, ':" . self::OLD_KEY . ":', ':" . self::NEW_KEY . ":')"),
                    ]);
            }

            $this->replaceKeyInChangeLogs(self::OLD_KEY, self::NEW_KEY);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $this->ensureTargetKeyIsAvailable(self::NEW_KEY, self::OLD_KEY);

            if (Schema::hasTable('scheduled_operation_definitions')) {
                DB::table('scheduled_operation_definitions')
                    ->where('task_key', self::NEW_KEY)
                    ->update([
                        'task_key' => self::OLD_KEY,
                        'handler_key' => self::OLD_KEY,
                        'name' => self::OLD_NAME,
                        'updated_at' => now(),
                    ]);
            }

            if (Schema::hasTable('scheduled_task_settings')) {
                DB::table('scheduled_task_settings')
                    ->where('task_key', self::NEW_KEY)
                    ->update(['task_key' => self::OLD_KEY, 'updated_at' => now()]);
            }

            if (Schema::hasTable('scheduled_runs')) {
                DB::table('scheduled_runs')
                    ->where('task_key', self::NEW_KEY)
                    ->update(['task_key' => self::OLD_KEY, 'task_name' => self::OLD_NAME]);

                DB::table('scheduled_runs')
                    ->where('lock_key', 'like', '%:' . self::NEW_KEY . ':%')
                    ->update([
                        'lock_key' => DB::raw("REPLACE(lock_key, ':" . self::NEW_KEY . ":', ':" . self::OLD_KEY . ":')"),
                    ]);
            }

            $this->replaceKeyInChangeLogs(self::NEW_KEY, self::OLD_KEY);
        });
    }

    private function ensureTargetKeyIsAvailable(string $sourceKey, string $targetKey): void
    {
        foreach (['scheduled_operation_definitions', 'scheduled_task_settings'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $sourceExists = DB::table($table)->where('task_key', $sourceKey)->exists();
            $targetExists = DB::table($table)->where('task_key', $targetKey)->exists();

            if ($sourceExists && $targetExists) {
                throw new \RuntimeException("Cannot rename scheduled operation [$sourceKey] because [$targetKey] already exists in [$table].");
            }
        }
    }

    private function replaceKeyInChangeLogs(string $from, string $to): void
    {
        if (!Schema::hasTable('scheduled_operation_change_logs')) {
            return;
        }

        foreach (['before', 'after'] as $column) {
            DB::statement(
                "UPDATE scheduled_operation_change_logs SET `$column` = REPLACE(`$column`, ?, ?) WHERE `$column` LIKE ?",
                [$from, $to, '%' . $from . '%']
            );
        }
    }
};
