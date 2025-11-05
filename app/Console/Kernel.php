<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\NightlyUpdate::class,
        \App\Console\Commands\HourlyUpdate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('cache:prune-stale-tags')->hourly();
        if (\App::environment('prod')) {
            $schedule->command('backup:clean -n')->weekly()->mondays()->at('00:01');
            $schedule->command('backup:run -n')->daily()->at('00:02');
            $schedule->command('app:nightly-update')->daily()->at('00:05');
            $schedule->command('app:nightly-verify')->daily()->at('00:30');
            $schedule->command('app:hourly-update')->hourlyAt(01);  // Every hour at minute 1;
            $schedule->command('app:hourly-update')->everyMinute();  // Every hour at minute 1;
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
