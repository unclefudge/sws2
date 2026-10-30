<?php

namespace App\Observers;

use App\Models\Site\SiteCc;

class SiteCcObserver
{
    public function saved(SiteCc $cc): void
    {
        if ($cc->wasRecentlyCreated || $cc->wasChanged(['cc_requested', 'wbo_waiting', 'status'])) {
            $cc->syncStage();
        }
    }
}
