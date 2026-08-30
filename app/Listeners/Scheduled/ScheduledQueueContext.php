<?php

namespace App\Listeners\Scheduled;

use App\Models\Scheduled\ScheduledReportMessage;
use App\Models\Scheduled\ScheduledRun;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledRunContext;
use App\Scheduled\ScheduledRunSummary;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;

/**
 * Carries a scheduled run id through child queue jobs.
 *
 * Several legacy reports queue a Mailable or a PDF batch and return before the
 * email is actually sent. The payload metadata registered in AppServiceProvider
 * lets these listeners reopen the correct run context in the worker later.
 */
class ScheduledQueueContext
{
    public function __construct(
        private ScheduledRunContext $context,
        private ScheduledDynamicRecipientContext $dynamicContext
    ) {
    }

    public function processing(JobProcessing $event): void
    {
        // Queue workers are long-lived, so always clear the previous job first.
        $this->clear();
        $payload = $event->job->payload();

        if ($runId = $this->runIdFrom($payload)) {
            $this->context->begin($runId);

            if ($recipients = $this->dynamicRecipientsFrom($payload)) {
                $this->dynamicContext->begin($recipients);
            }
        }
    }

    public function processed(JobProcessed $event): void
    {
        $this->clear();
    }

    public function exceptionOccurred(JobExceptionOccurred $event): void
    {
        // This event may be followed by a retry, so do not mark the parent run
        // failed here. Record any mail attempt that had already started, while
        // JobFailed below handles the final parent result after all retries.
        if ($runId = $this->runIdFrom($event->job->payload())) {
            ScheduledReportMessage::where('scheduled_run_id', $runId)
                ->where('status', 'sending')
                ->update(['status' => 'failed', 'failed_at' => now(), 'error' => $event->exception->getMessage()]);
        }

        $this->clear();
    }

    public function failed(JobFailed $event): void
    {
        $runId = $this->runIdFrom($event->job->payload());

        $run = $runId ? ScheduledRun::find($runId) : null;

        if (!$run) {
            $this->clear();
            return;
        }

        // A parent report can finish successfully after merely queueing its
        // email. If that child job ultimately fails, reflect the real outcome.
        $run->update([
            'status' => 'failed',
            'completed_at' => now(),
            'exception_class' => get_class($event->exception),
            'exception_message' => 'Child queue job failed: ' . $event->exception->getMessage(),
            'exception_file' => $event->exception->getFile(),
            'exception_line' => $event->exception->getLine(),
            'exception_trace' => mb_substr($event->exception->getTraceAsString(), 0, config('scheduled_operations.max_output_bytes')),
            // A later failure needs its own alert even if this run was viewed.
            'failure_notified_at' => null,
        ]);

        ScheduledReportMessage::where('scheduled_run_id', $runId)
            ->where('status', 'sending')
            ->update(['status' => 'failed', 'failed_at' => now(), 'error' => $event->exception->getMessage()]);

        app(ScheduledRunSummary::class)->refresh($run->group);
        $this->clear();
    }

    private function runIdFrom(array $payload): ?int
    {
        $runId = $payload['sws_scheduled_run_id'] ?? null;

        return is_numeric($runId) ? (int) $runId : null;
    }

    private function dynamicRecipientsFrom(array $payload): array
    {
        $recipients = $payload['sws_scheduled_dynamic_recipients'] ?? [];

        return is_array($recipients) ? $recipients : [];
    }

    private function clear(): void
    {
        $this->context->end();
        $this->dynamicContext->end();
    }
}
