<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteProjectSupplyOverdue;
use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\SiteProjectSupply;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;

class ProjectSupplyOverdueReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.project_supply_overdue',
            'name' => 'Overdue project supplies',
            'category' => 'report',
            'description' => 'Emails active project supplies that remain incomplete after old lockup or practical-completion planner tasks.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: site.projectsupply.overdue',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find active project supplies associated with planner milestones older than 14 days.
     */
    public function handle(): int
    {
        $cutoff = Carbon::today()->subDays(14);
        $activeSiteIds = SiteProjectSupply::query()->where('status', 1)->pluck('site_id')->unique();

        if ($activeSiteIds->isEmpty()) {
            echo "No active project supplies. No email required.\n";
            return 0;
        }

        // 265 = Practical Completion; 117 = Lockup/Dismantle Scaffold.
        $milestones = SitePlanner::query()->whereDate('from', '<', $cutoff)->whereIn('site_id', $activeSiteIds)
            ->whereIn('task_id', [117, 265])->get(['site_id', 'task_id']);
        $practicalCompletionSiteIds = $milestones->where('task_id', 265)->pluck('site_id')->unique();
        $lockupSiteIds = $milestones->where('task_id', 117)->pluck('site_id')->unique();
        $relevantSiteIds = $practicalCompletionSiteIds->merge($lockupSiteIds)->unique();
        $supplies = SiteProjectSupply::query()->whereIn('site_id', $relevantSiteIds)->get();

        $practicalCompletionProjects = $supplies->whereIn('site_id', $practicalCompletionSiteIds)->values();
        $lockupProjects = $supplies->whereIn('site_id', $lockupSiteIds)->filter(fn(SiteProjectSupply $supply) => !$supply->lockupCompleted())->values()->all();

        echo "Practical-completions: {$practicalCompletionProjects->count()}\n";
        echo 'Lockup: ' . count($lockupProjects) . "\n";

        if ($practicalCompletionProjects->isEmpty() && !$lockupProjects) {
            echo "No email required.\n";
            return 0;
        }

        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.projectsupply.overdue');
        $mailable = new SiteProjectSupplyOverdue($lockupProjects, $practicalCompletionProjects);
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo "Overdue Project Supplies report sent.\n";

        return 1;
    }
}
