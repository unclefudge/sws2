<?php

namespace App\Console\Commands;

use App\Scheduled\ScheduledOperationRegistry;
use Illuminate\Console\Command;

class SyncScheduledOperations extends Command
{
    protected $signature = 'scheduled:sync {--update-metadata : Refresh names and descriptions without changing schedules or recipients}';
    protected $description = 'Import safe scheduled-operation handlers into database-managed configuration';

    public function handle(ScheduledOperationRegistry $registry): int
    {
        $result = $registry->syncDefinitions((bool) $this->option('update-metadata'));
        $this->info("Created {$result['created']} operation(s); refreshed {$result['updated']}; preserved {$result['preserved']} configured operation(s).");

        return self::SUCCESS;
    }
}
