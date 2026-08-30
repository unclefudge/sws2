<?php

namespace App\Scheduled\Operations;

use App\Models\Site\SiteFoc;
use App\Scheduled\Contracts\ScheduledOperationHandler;

class FocStagesSyncOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'hourly.sync_foc_stages',
            'name' => 'Synchronise FOC stages',
            'category' => 'hourly',
            'description' => 'Reconciles each FOC stage with its current site data.',
            'schedule' => ['type' => 'hourly', 'minute' => 1],
            'recipients' => 'No email is sent by this operation',
            'sendsEmail' => false,
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $updated = 0;

        SiteFoc::query()->with('site')->chunkById(200, function ($focs) use (&$updated) {
            foreach ($focs as $foc) {
                if ($foc->syncStage()) $updated++;
            }
        });

        echo "FOC stages updated: {$updated}\n";

        return $updated;
    }
}
