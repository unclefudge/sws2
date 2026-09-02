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
        if (app()->environment('prod')) {
            $schedule->command('backup:clean -n')->weekly()->mondays()->at('00:01');
            $schedule->command('backup:run -n')->daily()->at('00:02');
            // Scheduled Operations / Reports
            $schedule->command('scheduled:dispatch')->everyMinute()->withoutOverlapping(10);

            // This independent monitor retries its own email on the next pass
            // if the mail server is temporarily unavailable.
            $schedule->command('scheduled:monitor')->everyFiveMinutes()->withoutOverlapping(10);

            // Create audit rows. Keep the history bounded without relying on a manual database clean-up.
            $schedule->command('scheduled:prune')->daily()->at('01:20')->withoutOverlapping(30);
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
