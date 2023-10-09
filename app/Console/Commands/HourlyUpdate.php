<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HourlyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hourly-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hourly update of SafeWorksite tasks / reminders';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //\Log::info('Nightly update of roster + non-compliance');
        \App\Http\Controllers\Misc\CronTaskController::hourly();
        return Command::SUCCESS;
    }
}
