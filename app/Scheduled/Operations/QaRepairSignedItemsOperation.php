<?php

namespace App\Scheduled\Operations;

use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\SiteQaItem;
use App\Scheduled\Contracts\ScheduledOperationHandler;

class QaRepairSignedItemsOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.broken_qa_items',
            'name' => 'Repair signed QA items',
            'category' => 'maintenance',
            'description' => 'Marks signed QA items complete when their stored status was not updated and restores the responsible planner company where possible.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        // A signature proves the item was completed. These are legacy records
        // where sign_by was saved but status remained incomplete and done_by
        // was not populated.
        $items = SiteQaItem::query()->with(['document.site'])->whereNull('done_by')->where('status', 0)->where('sign_by', '>', 0)
            ->whereHas('document', fn($query) => $query->where('master', 0)->where('status', '>', 0))->orderBy('id')->get();
        $plannerTasks = SitePlanner::query()->whereIn('site_id', $items->pluck('document.site_id')->filter()->unique())
            ->whereIn('task_id', $items->pluck('task_id')->filter()->unique())->orderBy('id')->get()
            ->groupBy(fn(SitePlanner $plan) => $this->plannerKey($plan->site_id, $plan->task_id))->map(fn($records) => $records->first());
        $repairedCount = 0;
        $companyAttributedCount = 0;
        $withoutCompanyAttributionCount = 0;

        echo "Inconsistent signed QA items found: {$items->count()}.\n";

        foreach ($items as $item) {
            $qa = $item->document;
            if (!$qa) {
                echo "Skipped QA item [{$item->id}]: its QA report was deleted during processing.\n";
                continue;
            }

            $plannedTask = $plannerTasks->get($this->plannerKey($qa->site_id, $item->task_id));
            $item->status = 1;

            // Non-supervisor items belong to the company assigned to the
            // matching planner task. Supervisor-owned items deliberately keep
            // done_by empty, matching the original repair behaviour.
            if ($plannedTask && $plannedTask->entity_type === 'c' && (int)$item->super === 0) {
                $item->done_by = $plannedTask->entity_id;
                $companyAttributedCount++;
            } else {
                $withoutCompanyAttributionCount++;
            }

            $item->save();
            $repairedCount++;
            $siteName = $qa->site?->name ?? "Deleted site #{$qa->site_id}";
            $doneBy = $item->done_by ?? 'not assigned';
            echo "Repaired QA item [{$item->id}] {$item->name}; QA [{$qa->id}] {$qa->name}; site {$siteName}; done_by {$doneBy}.\n";
        }

        echo "QA items repaired: {$repairedCount}.\n";
        echo "Items attributed to a planner company: {$companyAttributedCount}.\n";
        echo "Items completed without company attribution: {$withoutCompanyAttributionCount}.\n";

        return $repairedCount;
    }

    private function plannerKey($siteId, $taskId): string
    {
        return "{$siteId}:{$taskId}";
    }
}
