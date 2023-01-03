<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NightlyVerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:nightly-verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify Nightly update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info('Verifying Nightly');
        \App\Http\Controllers\Misc\CronController::verifyNightly();

        return 0;
    }
}
