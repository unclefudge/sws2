<?php

namespace App\Observers;

use App\Models\Site\SiteCdc;

class SiteCdcObserver
{
    public function saved(SiteCdc $cdc): void
    {
        if ($cdc->wasRecentlyCreated || $cdc->wasChanged(['cdc_requested', 'wbo_waiting', 'status'])) {
            $cdc->syncStage();
        }
    }
}
