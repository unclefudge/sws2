<?php

namespace App\Console\Commands;

use App\Models\Scheduled\ScheduledRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MonitorScheduledOperations extends Command
{
    protected $signature = 'scheduled:monitor';
    protected $description = 'Email the SafeWorksite administrator about scheduled operations that exhausted their retries';

    public function handle(): int
    {
        $email = config('scheduled_operations.alert_email');

        if (!$email) {
            $this->warn('SCHEDULED_OPERATIONS_ALERT_EMAIL is not configured.');
            return self::SUCCESS;
        }

        $runs = ScheduledRun::whereIn('status', ['failed', 'missed'])
            ->whereNull('failure_notified_at')
            ->where('completed_at', '<=', now()->subMinutes(config('scheduled_operations.failure_alert_delay_minutes')))
            ->orderBy('completed_at')
            ->get();

        foreach ($runs as $run) {
            try {
                $body = "A SafeWorksite scheduled operation {$run->status}.\n\n"
                    . "Operation: {$run->task_name}\nKey: {$run->task_key}\n"
                    . "Scheduled: {$run->scheduled_for}\nAttempt: {$run->attempt}\n\n"
                    . ($run->status === 'failed' ? "Error: {$run->exception_message}\n{$run->exception_file}:{$run->exception_line}\n\n" : "The scheduler was unavailable when this operation was due. It was not run automatically.\n\n")
                    . url('/manage/scheduled-operations');

                $state = $run->status === 'missed' ? 'missed' : 'failed';
                Mail::raw($body, fn($message) => $message->to($email)->subject("SafeWorksite job {$state}: {$run->task_name}"));
                $run->update(['failure_notified_at' => now()]);
            } catch (\Throwable $exception) {
                // Leave failure_notified_at empty so the next monitor pass retries.
                report($exception);
            }
        }

        return self::SUCCESS;
    }
}
