<?php

namespace App\Scheduled\Operations;

use App\Models\Comms\Todo;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\SiteScaffoldHandover;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Carbon\Carbon;
use RuntimeException;

class PlannerKeyActionsCreateOperation implements ScheduledOperationHandler
{
    private const SCAFFOLD_UP_TASK_IDS = [220, 24];
    private const SCAFFOLD_REVIEWER_ID = 1032;
    private const COMPANY_ID = 3;

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.planner_key_actions',
            'name' => 'Create planner key actions',
            'category' => 'maintenance',
            'description' => 'Creates a Scaffold Handover Certificate and assigned review ToDo when a Scaffold Up planner task begins.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Assigned scaffold reviewer and recipients resolved by the Scaffold Handover notification workflow',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $reviewer = User::query()->whereKey(self::SCAFFOLD_REVIEWER_ID)->where('status', 1)->first();
        if (!$reviewer) throw new RuntimeException('The configured Scaffold Handover reviewer user [1032] is missing or inactive.');

        $plans = SitePlanner::query()->with('site')->whereDate('from', Carbon::today())->whereIn('task_id', self::SCAFFOLD_UP_TASK_IDS)
            ->whereHas('site', fn($query) => $query->where('status', 1))->orderBy('site_id')->get();
        $activeHandoverSiteIds = SiteScaffoldHandover::query()->where('status', 1)->whereIn('site_id', $plans->pluck('site_id')->unique())
            ->pluck('site_id')->mapWithKeys(fn($siteId) => [(int)$siteId => true]);
        $createdCount = 0;

        echo "Active Scaffold Up planner tasks today: {$plans->count()}.\n";

        foreach ($plans as $plan) {
            $site = $plan->site;
            if ($activeHandoverSiteIds->has((int)$site->id)) {
                echo "Skipped [{$site->id}] {$site->name}: an active Scaffold Handover Certificate already exists.\n";
                continue;
            }

            $handover = SiteScaffoldHandover::create(['site_id' => $site->id]);
            $todo = Todo::create([
                'type' => 'scaffold handover', 'type_id' => $handover->id, 'name' => "Scaffold Handover Certificate for {$site->name}",
                'info' => "Please complete the Scaffold Handover Certificate for {$site->name}", 'priority' => 1,
                'due_at' => nextWorkDate(Carbon::today(), '+', 1)->toDateTimeString(), 'company_id' => self::COMPANY_ID, 'created_by' => 1, 'updated_by' => 1,
            ]);
            $todo->assignUsers($reviewer->id);
            $todo->emailToDo('ASSIGNED');
            $handover->emailReportCreated();
            $activeHandoverSiteIds->put((int)$site->id, true);
            $createdCount++;

            echo "Created Scaffold Handover Certificate [{$handover->id}] and ToDo [{$todo->id}] for [{$site->id}] {$site->name}; assigned to {$reviewer->fullname}.\n";
        }

        if (!$createdCount) echo "No new Scaffold Handover actions were required today.\n";
        echo "Scaffold Handover Certificates and ToDos created: {$createdCount}.\n";

        return $createdCount;
    }
}
