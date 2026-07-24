<?php

namespace App\Observers;

use App\Models\Site\Site;
use App\Models\Site\SiteFoc;

class SiteObserver
{
    public function saved(Site $site): void
    {
        if (!$site->wasChanged(['status', 'completion_signed', 'oc_rcvd_date',])) {
            return;
        }

        $foc = SiteFoc::where('site_id', $site->id)->first();

        if ($foc) {
            $foc->syncStage();
        }
    }
}