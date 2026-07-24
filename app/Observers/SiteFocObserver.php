<?php

namespace App\Observers;

use App\Models\Site\SiteFoc;

class SiteFocObserver
{
    public function saved(SiteFoc $foc): void
    {
        if ($foc->wasChanged(['foc_requested', 'wbo_waiting', 'status',])) {
            $foc->syncStage();
        }
    }
}