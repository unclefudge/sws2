<?php

namespace App\Scheduled\Operations;

use App\Models\Misc\Action;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\SiteAsbestos;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;

class AsbestosNotificationsCreateOperation implements ScheduledOperationHandler
{
    private const ASBESTOS_TASK_IDS = [19, 213, 723];

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.asbestos_notifications',
            'name' => 'Create asbestos notifications',
            'category' => 'notifications',
            'description' => 'Creates asbestos notifications and assigned Supervisor ToDos for upcoming asbestos planner tasks, then keeps their dates synchronised with the planner.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Affected Site Supervisors through their assigned ToDos',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $plans = SitePlanner::query()->with(['site.supervisor'])->whereDate('from', '>', Carbon::today())
            ->whereIn('task_id', self::ASBESTOS_TASK_IDS)->whereHas('site', fn($query) => $query->where('status', 1))->orderBy('site_id')->orderBy('from')->get();
        $notifications = SiteAsbestos::query()->whereIn('plan_id', $plans->pluck('id'))->get()->keyBy('plan_id');
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        echo "Upcoming asbestos planner tasks found: {$plans->count()}.\n";

        foreach ($plans as $plan) {
            $site = $plan->site;
            $notification = $notifications->get($plan->id);

            if ($notification) {
                if ($this->dateKey($notification->date_from) === $this->dateKey($plan->from) && $this->dateKey($notification->date_to) === $this->dateKey($plan->to)) continue;

                $notification->date_from = $plan->from;
                $notification->date_to = $plan->to;
                $notification->save();
                Action::create(['action' => 'Updated dates due to Planner task moving', 'table' => 'site_asbestos', 'table_id' => $notification->id, 'created_by' => 1, 'updated_by' => 1]);
                $updatedCount++;
                echo "Updated asbestos notification [{$notification->id}] for {$site->name}; planner task [{$plan->id}].\n";
                continue;
            }

            $supervisor = $site?->supervisor;
            if (!$site || !$supervisor) {
                $skippedCount++;
                $siteName = $site?->name ?? "Deleted site #{$plan->site_id}";
                echo "Skipped planner task [{$plan->id}] for {$siteName}: no Site Supervisor is assigned.\n";
                continue;
            }

            $notification = SiteAsbestos::create([
                'site_id' => $site->id, 'supervisor_id' => $supervisor->id, 'super_phone' => $supervisor->phone,
                'client_name' => $site->client1_name, 'client_phone' => $site->client1_mobile,
                'plan_id' => $plan->id, 'date_from' => $plan->from, 'date_to' => $plan->to,
            ]);
            Action::create(['action' => 'Created Notification', 'table' => 'site_asbestos', 'table_id' => $notification->id, 'created_by' => 1, 'updated_by' => 1]);
            $notification->touch();
            $notification->createAssignSupervisorToDo($supervisor->id);
            $notifications->put($plan->id, $notification);
            $createdCount++;
            echo "Created asbestos notification [{$notification->id}] for {$site->name}; planner task [{$plan->id}].\n";
        }

        echo "Asbestos notifications created: {$createdCount}; dates updated: {$updatedCount}; skipped: {$skippedCount}.\n";

        return $createdCount + $updatedCount;
    }

    private function dateKey($date): ?string
    {
        return $date ? Carbon::parse($date)->toDateString() : null;
    }
}
