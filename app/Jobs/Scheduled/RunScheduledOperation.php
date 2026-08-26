<?php

namespace App\Jobs\Scheduled;

use App\Models\Scheduled\ScheduledReportMessage;
use App\Models\Scheduled\ScheduledRun;
use App\Scheduled\ScheduledOperationRegistry;
use App\Scheduled\ScheduledRunContext;
use App\Scheduled\ScheduledRunSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RunScheduledOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $timeout;

    public function __construct(public int $runId, int $tries = 3, int $timeout = 240)
    {
        // Queue retry/timeout settings are snapshotted at dispatch. Editing an
        // operation later cannot unexpectedly change an already queued attempt.
        $this->tries = max(1, $tries);
        $this->timeout = max(30, $timeout);
    }

    public function handle(ScheduledOperationRegistry $registry, ScheduledRunContext $context, ScheduledRunSummary $summary): void
    {
        $run = ScheduledRun::findOrFail($this->runId);
        $started = microtime(true);

        $run->update([
            'status' => 'running',
            'started_at' => now(),
            'completed_at' => null,
            'attempt' => $this->attempts(),
            'exception_class' => null,
            'exception_message' => null,
            'exception_file' => null,
            'exception_line' => null,
            'exception_trace' => null,
        ]);
        $summary->refresh($run->group);

        // Legacy methods echo useful progress information. Buffering it keeps the
        // queue worker quiet while preserving that detail in the web interface.
        ob_start();
        $context->begin($run->id);

        try {
            $result = $registry->execute($run->task_key);
            $output = (string) ob_get_clean();
            $limit = config('scheduled_operations.max_output_bytes');

            if ($result !== null && $result !== 0 && $result !== '') {
                $output .= "\nReturn value: " . (is_scalar($result) ? $result : json_encode($result));
            }

            // A report such as FOC may deliberately continue after one recipient
            // fails so the remaining recipients still receive their emails. Mark
            // that attempt failed for monitoring, but do not throw and retry the
            // entire report because that would duplicate emails already delivered.
            $failedEmails = ScheduledReportMessage::where('scheduled_run_id', $run->id)
                ->where('status', 'failed')
                ->count();

            $run->update([
                'status' => $failedEmails ? 'failed' : 'successful',
                'completed_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'output' => mb_substr($output, 0, $limit),
                'exception_message' => $failedEmails
                    ? "$failedEmails report email(s) failed after the remaining recipients were processed."
                    : null,
            ]);
        } catch (Throwable $exception) {
            $output = (string) ob_get_clean();
            $run->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'output' => mb_substr($output, 0, config('scheduled_operations.max_output_bytes')),
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'exception_file' => $exception->getFile(),
                'exception_line' => $exception->getLine(),
                'exception_trace' => mb_substr($exception->getTraceAsString(), 0, config('scheduled_operations.max_output_bytes')),
            ]);

            // If mail construction failed after MessageSending, avoid leaving a
            // misleading "sending" record in the email history.
            ScheduledReportMessage::where('scheduled_run_id', $run->id)->where('status', 'sending')->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        } finally {
            $context->end();
            $summary->refresh($run->fresh()->group);
        }
    }

    public function failed(Throwable $exception): void
    {
        $run = ScheduledRun::find($this->runId);

        if (!$run) {
            return;
        }

        // failed() runs only after the worker has exhausted all retries.
        $run->update([
            'status' => 'failed',
            'completed_at' => now(),
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'exception_trace' => mb_substr($exception->getTraceAsString(), 0, config('scheduled_operations.max_output_bytes')),
        ]);

        app(ScheduledRunSummary::class)->refresh($run->group);
    }
}
