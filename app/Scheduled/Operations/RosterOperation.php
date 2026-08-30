<?php

namespace App\Scheduled\Operations;

use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;

class RosterOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.roster',
            'name' => 'Build today\'s roster',
            'category' => 'attendance',
            'description' => 'Adds active staff from companies planned on active sites to today\'s site roster.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'sendsEmail' => false,
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $date = $today->toDateString();
        $planner = SitePlanner::query()->where('entity_type', 'c')->whereDate('from', '<=', $today)->whereDate('to', '>=', $today)->orderBy('site_id')->get();
        $sites = Site::query()->whereIn('id', $planner->pluck('site_id')->unique())->where('status', 1)->where('code', '<>', '0007')->get()->keyBy('id');
        $companies = Company::query()->whereIn('id', $planner->pluck('entity_id')->unique())->get()->keyBy('id');
        $tasks = Task::query()->whereIn('id', $planner->pluck('task_id')->unique())->get()->keyBy('id');
        $staffByCompany = [];

        // Resolve each company's active staff once even when it has several planner entries or tasks on the same day.
        foreach ($companies as $company) $staffByCompany[$company->id] = $company->staffStatus(1);

        $staffIds = collect($staffByCompany)->flatten(1)->pluck('id')->unique();
        $existingRoster = SiteRoster::query()->whereDate('date', $today)->whereIn('site_id', $sites->keys())->whereIn('user_id', $staffIds)->get()
            ->mapWithKeys(fn(SiteRoster $entry) => [$entry->site_id . ':' . $entry->user_id => true]);
        $createdCount = 0;

        foreach ($planner as $plan) {
            $site = $sites->get($plan->site_id);
            if (!$site) continue;

            $company = $companies->get($plan->entity_id);
            if (!$company) {
                echo "Skipped planner {$plan->id}: assigned company {$plan->entity_id} no longer exists.\n";
                continue;
            }

            $taskName = $tasks->get($plan->task_id)?->name ?: 'Unknown task';
            echo "Site: {$site->name} ({$site->id}); Company: {$company->name_alias}; Task: {$taskName}; Planner: {$plan->id}.\n";
            $planCreatedCount = 0;

            foreach ($staffByCompany[$company->id] as $user) {
                $key = $site->id . ':' . $user->id;
                if ($existingRoster->has($key)) continue;

                // firstOrCreate makes ordinary retries idempotent if the
                // in-memory roster snapshot was taken before an earlier insert.
                $rosterEntry = SiteRoster::firstOrCreate(['site_id' => $site->id, 'user_id' => $user->id, 'date' => $date . ' 00:00:00',],
                    ['created_by' => 1, 'updated_by' => 1,]);
                $existingRoster->put($key, true);

                if (!$rosterEntry->wasRecentlyCreated) continue;

                echo "Added {$user->fullname} ({$user->username}) to the roster.\n";
                $createdCount++;
                $planCreatedCount++;
            }

            if (!$planCreatedCount) echo "No users required adding for this planner entry.\n";
        }

        echo "Roster entries created: {$createdCount}.\n";

        return $createdCount;
    }
}
