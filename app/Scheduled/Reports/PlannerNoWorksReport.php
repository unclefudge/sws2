<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteNoWorksPlanned;
use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Site;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;

class PlannerNoWorksReport implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.no_works_planned',
            'name' => 'No works planned',
            'category' => 'report',
            'description' => 'Emails a supervisor-grouped list of active sites with no planner work during the next 14 days.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [2], 'time' => '00:05'], // Tuesday
            'recipients' => 'Legacy notification group: site.nowork.planned; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $twoWeeks = $today->copy()->addDays(14);
        $capeCod = Company::findOrFail(3);
        $emailList = $capeCod->notificationsUsersEmailType('site.nowork.planned');
        $supervisors = $capeCod->supervisors()->where('status', 1)->reject(fn($supervisor) => $supervisor->name === 'TO BE ALLOCATED')->sortBy('firstname');
        $plannedSiteIds = SitePlanner::query()->whereDate('from', '>=', $today)->whereDate('from', '<=', $twoWeeks)->pluck('site_id')->unique();

        // Load all qualifying sites once, then arrange them into the same
        // supervisor-grouped structure expected by the existing mailable.
        $sitesBySupervisor = Site::query()->where('status', 1)->whereIn('supervisor_id', $supervisors->pluck('id'))
            ->whereNotIn('id', $plannedSiteIds)->get()->groupBy('supervisor_id');
        $report = [];

        foreach ($supervisors as $supervisor) {
            $sites = $sitesBySupervisor->get($supervisor->id, collect());
            if ($sites->isNotEmpty()) $report[$supervisor->id] = ['supervisor' => $supervisor, 'sites' => $sites];
        }

        $mailable = new SiteNoWorksPlanned($report);
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo 'No Works Planned report sent: ' . count($report) . " supervisor group(s).\n";

        return 1;
    }
}
