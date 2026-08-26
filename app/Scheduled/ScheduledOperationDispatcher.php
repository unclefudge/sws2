<?php

namespace App\Scheduled;

use App\Jobs\Scheduled\RunScheduledOperation;
use App\Models\Scheduled\ScheduledRun;
use App\Models\Scheduled\ScheduledRunGroup;
use App\Models\Scheduled\ScheduledDispatchHeartbeat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ScheduledOperationDispatcher
{
    public function __construct(private ScheduledOperationRegistry $registry)
    {
    }

    public function dispatchWindow(Carbon $now, bool $shadow = false): int
    {
        $mode = $shadow ? 'shadow' : 'live';
        $heartbeat = ScheduledDispatchHeartbeat::firstOrCreate(['mode' => $mode]);
        $start = $heartbeat->last_checked_at
            ? Carbon::parse($heartbeat->last_checked_at)->addMinute()->seconds(0)
            : $now->copy()->seconds(0);

        // A very old heartbeat should not suddenly dispatch months of reports.
        // Keep one day of visible missed records and let an admin deliberately rerun them.
        if ($start->lt($now->copy()->subDay())) {
            $start = $now->copy()->subDay()->seconds(0);
        }

        $groups = 0;
        for ($cursor = $start->copy(); $cursor->lte($now); $cursor->addMinute()) {
            $isMissed = $cursor->lt($now->copy()->subMinutes(2));
            if ($this->dispatchDue($cursor, $shadow, $isMissed)) {
                $groups++;
            }
        }

        $heartbeat->update(['last_checked_at' => $now->copy()->seconds(0), 'last_success_at' => now()]);

        return $groups;
    }

    public function dispatchDue(Carbon $at, bool $shadow = false, bool $missed = false): ?ScheduledRunGroup
    {
        $definitions = collect($this->registry->allEffective())
            ->filter(fn(array $definition) => $definition['enabled'])
            ->filter(fn(array $definition) => $this->registry->isDue($definition, $at))
            ->reject(function (array $definition) use ($at, $shadow) {
                $lockKey = ($shadow ? 'shadow' : 'live') . ':' . $definition['key'] . ':' . $at->format('YmdHi');
                return ScheduledRun::where('lock_key', $lockKey)->exists();
            })
            ->values();

        if ($definitions->isEmpty()) {
            return null;
        }

        $result = DB::transaction(function () use ($definitions, $at, $shadow, $missed) {
            $runStatus = $missed ? 'missed' : ($shadow ? 'shadow' : 'queued');
            $group = ScheduledRunGroup::create([
                'uuid' => (string) Str::uuid(),
                'trigger' => 'scheduled',
                'mode' => $shadow ? 'shadow' : 'live',
                'status' => $missed ? 'missed' : ($shadow ? 'successful' : 'queued'),
                'scheduled_for' => $at->copy()->seconds(0),
                // Updated to the number actually inserted below. Starting at
                // zero also handles two scheduler processes racing safely.
                'expected_count' => 0,
                'failure_count' => 0,
                'skip_count' => 0,
                'completed_at' => ($shadow || $missed) ? now() : null,
            ]);

            $runIds = [];
            foreach ($definitions as $definition) {
                // The unique minute-level lock makes schedule:run safe if Forge invokes
                // it twice during a deploy or if two scheduler processes overlap.
                $lockKey = ($shadow ? 'shadow' : 'live') . ':' . $definition['key'] . ':' . $at->format('YmdHi');
                $inserted = ScheduledRun::query()->insertOrIgnore(array_merge(
                    ['lock_key' => $lockKey],
                    $this->runAttributes($definition, $group, $at, 'scheduled', $runStatus),
                    ['created_at' => now(), 'updated_at' => now()]
                ));

                if ($inserted) {
                    $runIds[] = ScheduledRun::where('lock_key', $lockKey)->value('id');
                }
            }

            // If another scheduler won every unique lock, discard this empty
            // group. The winning process already owns and will dispatch the work.
            if (!$runIds) {
                $group->delete();
                return null;
            }

            $group->update([
                'expected_count' => count($runIds),
                'failure_count' => $missed ? count($runIds) : 0,
                'skip_count' => $shadow && !$missed ? count($runIds) : 0,
            ]);

            return ['group' => $group, 'run_ids' => $runIds];
        });

        if (!$result) {
            return null;
        }

        // Dispatch only after the transaction commits. A very fast Forge worker
        // must never receive a run id before its database row is visible.
        if (!$shadow && !$missed) {
            foreach ($result['run_ids'] as $runId) {
                try {
                    $this->queueRun($runId);
                } catch (Throwable $exception) {
                    // queueRun has already recorded this individual failure.
                    // Continue so one malformed job cannot block the other due
                    // operations from reaching the worker.
                    report($exception);
                }
            }
        }

        return $result['group']->fresh();
    }

    public function dispatchManual(string $taskKey, ?int $requestedBy = null, ?int $retryOfId = null): ScheduledRun
    {
        $definition = $this->registry->find($taskKey);

        if (!$definition) {
            throw new \InvalidArgumentException("Unknown scheduled operation [$taskKey].");
        }

        $group = ScheduledRunGroup::create([
            'uuid' => (string) Str::uuid(),
            'trigger' => $retryOfId ? 'retry' : 'manual',
            'mode' => 'live',
            'status' => 'queued',
            'scheduled_for' => now(),
            'expected_count' => 1,
            'requested_by' => $requestedBy,
        ]);

        $run = ScheduledRun::create(array_merge(
            $this->runAttributes($definition, $group, Carbon::now(), $retryOfId ? 'retry' : 'manual', 'queued'),
            ['retry_of_id' => $retryOfId, 'requested_by' => $requestedBy]
        ));

        $this->queueRun($run->id);

        return $run;
    }

    private function runAttributes(array $definition, ScheduledRunGroup $group, Carbon $at, string $trigger, string $status): array
    {
        $attributes = [
            'scheduled_run_group_id' => $group->id,
            'task_key' => $definition['key'],
            'task_name' => $definition['name'],
            'category' => $definition['category'],
            'trigger' => $trigger,
            'status' => $status,
            'scheduled_for' => $at->copy()->seconds(0),
        ];

        // Shadow and missed rows are terminal immediately; recording a finish
        // time allows retention and failure alerts to treat them normally.
        if (in_array($status, ['shadow', 'missed'], true)) {
            $attributes['completed_at'] = now();
        }

        return $attributes;
    }

    private function queueRun(int $runId): void
    {
        try {
            $run = ScheduledRun::findOrFail($runId);
            $definition = $this->registry->find($run->task_key);
            $tries = (int) ($definition['tries'] ?? 3);
            $timeout = (int) ($definition['timeout'] ?? 240);

            RunScheduledOperation::dispatch($runId, $tries, $timeout)
                ->onQueue(config('scheduled_operations.queue'));
        } catch (Throwable $exception) {
            // A database/queue outage can prevent a job from being inserted at
            // all. Persist that as a normal failure so the monitor still emails
            // the administrator instead of leaving a permanent "queued" row.
            $run = ScheduledRun::find($runId);

            if ($run) {
                $run->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'exception_class' => get_class($exception),
                    'exception_message' => 'Unable to add job to queue: ' . $exception->getMessage(),
                    'exception_file' => $exception->getFile(),
                    'exception_line' => $exception->getLine(),
                    'exception_trace' => mb_substr($exception->getTraceAsString(), 0, config('scheduled_operations.max_output_bytes')),
                ]);

                app(ScheduledRunSummary::class)->refresh($run->group);
            }

            throw $exception;
        }
    }

}
