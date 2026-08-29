<?php

namespace App\Scheduled\Operations;

use App\Mail\Site\SiteScaffoldHandoverCreated;
use App\Models\Comms\Todo;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\SiteScaffoldHandover;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\User;
use Carbon\Carbon;
use RuntimeException;

class PlannerKeyActionsCreateOperation implements ScheduledOperationHandler
{
    private const SCAFFOLD_UP_TASK_IDS = [220, 24];

    public function __construct(
        private ScheduledDynamicRecipientResolver $recipientResolver,
        private ScheduledDynamicRecipientContext $recipientContext,
        private ScheduledReportMailer $mailer
    ) {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.planner_key_actions',
            'name' => 'Create planner key actions',
            'category' => 'maintenance',
            'description' => 'Creates a Scaffold Handover Certificate and assigned review ToDo when a Scaffold Up planner task begins.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Assigned scaffold reviewer and affected Site Supervisor; dashboard-configurable Scaffold Handover CC recipients',
            'dynamicRecipients' => [
                ['key' => 'scaffold_reviewer', 'label' => 'Assigned Scaffold Reviewer', 'delivery' => 'to', 'description' => 'The fixed reviewer assigned to the individual Scaffold Handover ToDo.', 'required' => true],
                ['key' => 'site_supervisor', 'label' => 'Affected Site Supervisor', 'delivery' => 'to', 'description' => 'The Supervisor assigned to the site for the new Scaffold Handover.', 'required' => true],
            ],
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $reviewer = User::query()->whereKey(1032)->where('status', 1)->first();
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
                'due_at' => nextWorkDate(Carbon::today(), '+', 1)->toDateTimeString(), 'company_id' => 3, 'created_by' => 1, 'updated_by' => 1,
            ]);
            $todo->assignUsers($reviewer->id);
            $reviewerRecipients = $this->recipientResolver->user('scaffold_reviewer', 'Assigned Scaffold Reviewer', $reviewer, 'to');
            $this->recipientContext->run($reviewerRecipients, fn() => $todo->emailToDo('ASSIGNED'));
            $supervisorRecipients = $this->recipientResolver->siteSupervisor('site_supervisor', 'Affected Site Supervisor', $site, 'to');
            $mailable = new SiteScaffoldHandoverCreated($handover);
            $mailable->cc(['ianscottewin@gmail.com', 'kirstie@capecod.com.au', 'michelle@capecod.com.au']);
            $this->mailer->send($mailable, $supervisorRecipients);
            $activeHandoverSiteIds->put((int)$site->id, true);
            $createdCount++;

            echo "Created Scaffold Handover Certificate [{$handover->id}] and ToDo [{$todo->id}] for [{$site->id}] {$site->name}; assigned to {$reviewer->fullname}.\n";
        }

        if (!$createdCount) echo "No new Scaffold Handover actions were required today.\n";
        echo "Scaffold Handover Certificates and ToDos created: {$createdCount}.\n";

        return $createdCount;
    }
}
