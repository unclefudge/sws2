<?php

namespace App\Scheduled\Operations;

use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QaInspectionsOperation implements ScheduledOperationHandler
{
    private const COMPANY_ID = 3;
    private const JOB_START_TASK_ID = 11;
    private const PRACTICAL_COMPLETION_TASK_ID = 265;
    private const NEW_TEMPLATE_MIN_ID = 101;
    private const JOB_START_CUTOFF = '2017-07-13';

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.qa',
            'name' => 'Trigger QA inspections',
            'category' => 'maintenance',
            'description' => 'Creates or reactivates site QA inspections when their linked planner tasks finish and reactivates outstanding QAs at Practical Completion.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'clientConfigurable' => false,
        ];
    }

    //---------------------------------------------------------------
    // Automatically creates and reactivates site QA inspections
    // based on completed construction planner tasks.
    //---------------------------------------------------------------
    public function handle(): int
    {
        $today = Carbon::today();
        $windowStart = $today->copy()->subDays(7);
        $yesterday = $today->copy()->subDay();
        $jobStartCutoff = Carbon::parse(self::JOB_START_CUTOFF)->startOfDay();
        $allowedSiteIds = Company::findOrFail(self::COMPANY_ID)->sites(1)->pluck('id');
        $templates = SiteQa::query()->with('items')->where('master', 1)->where('status', 1)->where('company_id', self::COMPANY_ID)
            ->where('id', '>=', self::NEW_TEMPLATE_MIN_ID)->orderBy('id')->get();

        if ($templates->isEmpty()) throw new RuntimeException('No active new-format QA templates were found. QA planner triggers cannot be processed safely.');

        $templatesByTask = $this->templatesByTask($templates);
        $plans = SitePlanner::query()->where('to', '<', $today->toDateString())->where('to', '>', $windowStart->toDateString())
            ->whereIn('site_id', $allowedSiteIds)->orderBy('site_id')->orderBy('to')->orderBy('id')->get();
        $sites = Site::query()->whereIn('id', $plans->pluck('site_id')->unique())->get()->keyBy('id');
        $startPlans = SitePlanner::query()->whereIn('site_id', $sites->keys())->where('task_id', self::JOB_START_TASK_ID)->orderBy('id')->get()
            ->groupBy('site_id')->map(fn($records) => $records->first());
        $siteQas = SiteQa::query()->whereIn('site_id', $sites->keys())->whereIn('master_id', $templates->pluck('id'))->orderBy('id')->get()
            ->groupBy(fn(SiteQa $qa) => $this->siteTemplateKey($qa->site_id, $qa->master_id))->map(fn($records) => $records->first());
        $createdCount = 0;
        $taskReactivatedCount = 0;
        $practicalCompletionReactivatedCount = 0;
        $missingStartSites = [];
        $preCutoffSites = [];

        echo "Active new QA templates: {$templates->count()}.\n";
        echo "Recently completed planner records checked: {$plans->count()}.\n";

        foreach ($plans as $plan) {
            $templatesForTask = $templatesByTask[(int)$plan->task_id] ?? [];
            if (!$templatesForTask) continue;

            $site = $sites->get($plan->site_id);
            if (!$site) continue;

            $startPlan = $startPlans->get($site->id);
            if (!$startPlan?->from) {
                $missingStartSites[$site->id] = $site->name;
                continue;
            }

            if (!Carbon::parse($startPlan->from)->gt($jobStartCutoff)) {
                $preCutoffSites[$site->id] = $site->name;
                continue;
            }

            foreach ($templatesForTask as $template) {
                $key = $this->siteTemplateKey($site->id, $template->id);
                $qa = $siteQas->get($key);

                if (!$qa) {
                    $qa = $this->createQa($template, $site);
                    $siteQas->put($key, $qa);
                    $createdCount++;
                    echo "Created QA [{$qa->id}] from template [{$template->id}] {$qa->name} for {$site->name}; planner task {$plan->task_code} ({$plan->task_id}).\n";
                    continue;
                }

                if ((int)$qa->status === 2 && Carbon::parse($plan->to)->isSameDay($yesterday)) {
                    $this->reactivateQa($qa, $site);
                    $taskReactivatedCount++;
                    echo "Reactivated QA [{$qa->id}] {$qa->name} for {$site->name}; planner task {$plan->task_code} ({$plan->task_id}) ended yesterday.\n";
                }
            }
        }

        // Practical Completion is intentionally processed after normal task
        // triggers. A fresh status query prevents the same on-hold QA being
        // reactivated twice when both events occur in this run.
        $practicalCompletionSiteIds = $plans->where('task_id', self::PRACTICAL_COMPLETION_TASK_ID)->pluck('site_id')->unique()->values();
        $onHoldAtPracticalCompletion = SiteQa::query()->whereIn('site_id', $practicalCompletionSiteIds)->where('master', 0)->where('status', 2)->orderBy('id')->get();

        foreach ($onHoldAtPracticalCompletion as $qa) {
            $site = $sites->get($qa->site_id);
            if (!$site) continue;

            $this->reactivateQa($qa, $site);
            $practicalCompletionReactivatedCount++;
            echo "Reactivated QA [{$qa->id}] {$qa->name} for {$site->name} because Practical Completion finished.\n";
        }

        foreach ($missingStartSites as $siteId => $siteName) echo "Skipped site [{$siteId}] {$siteName}: planner task 11 has no start date.\n";
        foreach ($preCutoffSites as $siteId => $siteName) echo "Skipped site [{$siteId}] {$siteName}: construction started on or before " . self::JOB_START_CUTOFF . ".\n";

        $reactivatedCount = $taskReactivatedCount + $practicalCompletionReactivatedCount;
        echo "QAs created: {$createdCount}.\n";
        echo "QAs reactivated from completed trigger tasks: {$taskReactivatedCount}.\n";
        echo "QAs reactivated at Practical Completion: {$practicalCompletionReactivatedCount}.\n";

        return $createdCount + $reactivatedCount;
    }

    private function templatesByTask($templates): array
    {
        $taskIds = $templates->flatMap(fn(SiteQa $template) => $template->items->pluck('task_id'))->filter()->map(fn($id) => (int)$id)->unique()->values();
        $validTaskIds = Task::query()->whereIn('id', $taskIds)->pluck('id')->map(fn($id) => (int)$id)->flip();
        $templatesByTask = [];

        foreach ($templates as $template) {
            foreach ($template->items->pluck('task_id')->filter()->map(fn($id) => (int)$id)->unique() as $taskId) {
                if (!$validTaskIds->has($taskId)) continue;
                $templatesByTask[$taskId][] = $template;
            }
        }

        ksort($templatesByTask);

        return $templatesByTask;
    }

    private function createQa(SiteQa $template, Site $site): SiteQa
    {
        return DB::transaction(function () use ($template, $site) {
            $qa = SiteQa::create([
                'name' => $template->name, 'site_id' => $site->id, 'version' => $template->version, 'master' => 0, 'master_id' => $template->id,
                'category_id' => $template->category_id, 'order' => $template->order, 'company_id' => $template->company_id, 'status' => 1,
                'created_by' => 1, 'updated_by' => 1,
            ]);

            foreach ($template->items as $item) {
                SiteQaItem::create([
                    'doc_id' => $qa->id, 'task_id' => $item->task_id, 'name' => $item->name, 'order' => $item->order,
                    'super' => $item->super, 'certification' => $item->certification, 'master' => 0, 'master_id' => $item->id,
                    'created_by' => 1, 'updated_by' => 1,
                ]);
            }

            $qa->createToDo($site->supervisor_id);

            return $qa;
        });
    }

    private function reactivateQa(SiteQa $qa, Site $site): void
    {
        DB::transaction(function () use ($qa, $site) {
            $qa->status = 1;
            $qa->save();
            $qa->createToDo($site->supervisor_id);
        });
    }

    private function siteTemplateKey(int $siteId, int $templateId): string
    {
        return "{$siteId}:{$templateId}";
    }
}
