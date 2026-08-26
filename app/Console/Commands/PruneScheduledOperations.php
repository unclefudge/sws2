<?php

namespace App\Console\Commands;

use App\Models\Scheduled\ScheduledReportMessage;
use App\Models\Scheduled\ScheduledReportRecipient;
use App\Models\Scheduled\ScheduledRun;
use App\Models\Scheduled\ScheduledRunGroup;
use Illuminate\Console\Command;

class PruneScheduledOperations extends Command
{
    protected $signature = 'scheduled:prune';
    protected $description = 'Remove old scheduled-operation history using the configured retention period';

    public function handle(): int
    {
        $cutoff = now()->subDays(max(1, (int) config('scheduled_operations.history_days')));
        $deletedRuns = 0;

        // Work in small chunks so retention never holds a large production lock.
        do {
            $runIds = ScheduledRun::where('scheduled_for', '<', $cutoff)->limit(500)->pluck('id');

            if ($runIds->isEmpty()) {
                break;
            }

            $messageIds = ScheduledReportMessage::whereIn('scheduled_run_id', $runIds)->pluck('id');
            ScheduledReportRecipient::whereIn('scheduled_report_message_id', $messageIds)->delete();
            ScheduledReportMessage::whereIn('id', $messageIds)->delete();
            $deletedRuns += ScheduledRun::whereIn('id', $runIds)->delete();
        } while (true);

        // Groups have no value once every child run has aged out.
        ScheduledRunGroup::where('scheduled_for', '<', $cutoff)->doesntHave('runs')->delete();

        $this->info("Removed {$deletedRuns} scheduled run(s) older than {$cutoff->format('d/m/Y')}.");

        return self::SUCCESS;
    }
}
