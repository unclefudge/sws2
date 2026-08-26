<?php

namespace App\Console\Commands;

use App\Scheduled\ScheduledOperationDispatcher;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DispatchScheduledOperations extends Command
{
    protected $signature = 'scheduled:dispatch {--at= : Test a specific date/time} {--shadow : Record due jobs without executing them}';
    protected $description = 'Dispatch each due SafeWorksite scheduled operation as an independent queue job';

    public function handle(ScheduledOperationDispatcher $dispatcher): int
    {
        $at = $this->option('at') ? Carbon::parse($this->option('at')) : Carbon::now();
        $shadow = (bool) $this->option('shadow');
        if ($this->option('at')) {
            $group = $dispatcher->dispatchDue($at, $shadow);
            $this->info($group ? "Created scheduled run group {$group->uuid}." : 'No scheduled operations are due.');
        } else {
            $groups = $dispatcher->dispatchWindow($at, $shadow);
            $this->info("Processed $groups due schedule minute(s).");
        }

        return self::SUCCESS;
    }
}
