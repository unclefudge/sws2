<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SitePlannerKeyTask;
use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledReportMailer;
use Carbon\Carbon;

class PlannerKeyTaskReport implements ScheduledOperationHandler
{
    private const TASKS = [
        4 => ['subject' => 'is now ready to inspect and review Packers and Floor Joist'],
        7 => ['subject' => 'is now ready to inspect and review the Frame and Roof'],
        117 => ['subject' => 'has now reached Lock up stage', 'body' => 'Please follow up the Supervisor to complete the details available for the Project Completion Docs for this project now', 'special' => true],
        265 => ['subject' => 'has now reached Practical Completion', 'body' => 'Please follow up the Supervisor to complete the required Project Completion Docs for this project now', 'special' => true],
    ];

    public function __construct(private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.planner_key_emails',
            'name' => 'Planner key-task notifications',
            'category' => 'report',
            'description' => 'Emails notifications when selected planner tasks begin, including Lock up and Practical Completion milestones.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Dashboard-configurable management To/CC recipients; Legacy mode retains the existing task-specific recipients',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $plans = SitePlanner::query()->with('site')->whereDate('from', Carbon::today())->whereIn('task_id', array_keys(self::TASKS))
            ->whereHas('site', fn($query) => $query->where('status', 1))->orderBy('site_id')->get()->groupBy('task_id');
        $legacyRecipients = Company::findOrFail(3)->notificationsUsersEmailType('site.planner.key.tasks');
        $emailsSent = 0;

        echo 'Active planner key tasks today: ' . $plans->flatten(1)->count() . ".\n";

        // Iterating the task definitions preserves the original task priority
        // while the grouped query avoids running a separate query per task.
        foreach (self::TASKS as $taskId => $definition) {
            foreach ($plans->get($taskId, collect()) as $plan) {
                $site = $plan->site;
                $subject = "{$site->name} {$definition['subject']}";
                $message = "{$site->name} " . ($definition['body'] ?? $definition['subject']);
                $mailable = new SitePlannerKeyTask($plan, $subject, $message);

                // Legacy and Append retain the two original recipient paths.
                // Managed mode replaces these fixed addresses with the rules
                // maintained on the scheduler for every planner milestone.
                if (!empty($definition['special'])) $mailable->to(['michelle@capecod.com.au', 'damian@capecod.com.au'])->cc(['kirstie@capecod.com.au', 'ross@capecod.com.au']);
                elseif ($legacyRecipients) $mailable->to($legacyRecipients);

                // Always submit the mailable so Managed recipients can send it
                // even if the old notification group is currently empty.
                $this->mailer->send($mailable);
                $emailsSent++;
                echo "Sent planner key-task notification for [{$site->id}] {$site->name}; task [{$taskId}] {$definition['subject']}.\n";
            }
        }

        if (!$emailsSent) echo "No planner key-task notifications were required today.\n";
        echo "Planner key-task emails sent: {$emailsSent}.\n";

        return $emailsSent;
    }
}
