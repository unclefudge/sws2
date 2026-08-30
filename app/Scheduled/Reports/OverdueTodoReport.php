<?php

namespace App\Scheduled\Reports;

use App\Mail\Safety\ToolboxTalkOverdue;
use App\Models\Comms\Todo;
use App\Models\Safety\ToolboxTalk;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use Carbon\Carbon;

class OverdueTodoReport implements ScheduledOperationHandler
{
    public function __construct(
        private ScheduledDynamicRecipientResolver $recipientResolver,
        private ScheduledDynamicRecipientContext $recipientContext,
        private ScheduledReportMailer $mailer
    ) {}

    public static function scheduledOperation(): array
    {
        return [
            // Retain the legacy key so this handler replaces CronController::overdueToDo
            // rather than appearing as a duplicate operation during migration.
            'key' => 'nightly.overdue_todos',
            'name' => 'Overdue toolbox ToDos',
            'category' => 'report',
            'description' => 'Emails overdue toolbox reminders to assigned users and sends one management notification for each affected toolbox talk.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05'], // Monday
            'recipients' => 'Assigned ToDo users plus Kirstie Silk, Ross Thomson and the affected Toolbox Talk creator; optional dashboard recipients',
            'dynamicRecipients' => [
                ['key' => 'todo_assignees', 'label' => 'Assigned ToDo users', 'delivery' => 'to', 'description' => 'The active users assigned to each individual overdue toolbox ToDo.', 'required' => true],
                ['key' => 'toolbox_management', 'label' => 'Toolbox overdue management recipients', 'delivery' => 'to', 'description' => 'kirstie@capecod.com.au and ross@capecod.com.au receive each Toolbox overdue summary.', 'required' => true],
                ['key' => 'toolbox_creator', 'label' => 'Affected Toolbox Talk creator', 'delivery' => 'to', 'description' => 'The user who created the affected Toolbox Talk, when that user has a valid email address.', 'required' => false],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $overdue = Todo::query()->where('status', 1)->where('type', 'toolbox')->whereDate('due_at', '<', $today)
            ->where('due_at', '<>', '0000-00-00 00:00:00')->orderBy('due_at')->get();
        $toolboxes = ToolboxTalk::query()
            ->with(['createdBy.company'])
            ->whereIn('id', $overdue->pluck('type_id')->unique())
            ->get()
            ->keyBy('id');
        $toolboxIds = [];
        $closedCount = 0;
        $emailsSent = 0;

        echo "Overdue active toolbox ToDos: {$overdue->count()}.\n";

        foreach ($overdue as $todo) {
            $toolbox = $toolboxes->get($todo->type_id);

            if (!$toolbox || (int) $toolbox->status !== 1) {
                // The source toolbox is deleted or no longer active, so its
                // outstanding reminder must not remain visible indefinitely.
                $todo->status = 0;
                $todo->done_at = Carbon::now();
                $todo->done_by = 1;
                $todo->save();
                $closedCount++;
                echo "Closed obsolete toolbox ToDo [{$todo->id}].\n";
                continue;
            }

            // Assigned users are specific to this ToDo and remain required
            // automatic recipients. Dashboard recipients are added separately.
            $dynamicRecipients = $this->recipientResolver->todoAssignees('todo_assignees', 'Assigned ToDo users', $todo, 'to');
            $this->recipientContext->run($dynamicRecipients, fn() => $todo->emailToDo());
            $emailsSent++;
            $toolboxIds[(int) $toolbox->id] = true;
            echo "Sent overdue reminder for ToDo [{$todo->id}] due {$todo->due_at->format('d/m/Y')}.\n";
        }

        $managementRecipients = $this->recipientResolver->emails(
            'toolbox_management',
            'Toolbox overdue management recipients',
            ['kirstie@capecod.com.au', 'ross@capecod.com.au'],
            'to',
            true
        );

        // Preserve the original single management notification per affected
        // toolbox, but keep its recipient selection and send visible here.
        foreach (array_keys($toolboxIds) as $toolboxId) {
            $toolbox = $toolboxes->get($toolboxId);
            if (!$toolbox) continue;

            $creatorRecipients = $this->recipientResolver->user(
                'toolbox_creator',
                'Affected Toolbox Talk creator',
                $toolbox->createdBy,
                'to',
                false
            );

            $this->mailer->send(
                new ToolboxTalkOverdue($toolbox),
                array_merge($managementRecipients, $creatorRecipients)
            );
            $emailsSent++;
            echo "Sent management notification for toolbox [{$toolbox->id}] {$toolbox->name}.\n";
        }

        $overdueQaCount = Todo::query()->where('status', 1)->where('type', 'qa')->whereDate('due_at', '<', $today)
            ->where('due_at', '<>', '0000-00-00 00:00:00')->count();
        if ($overdueQaCount) echo "Overdue QA ToDos detected: {$overdueQaCount}; the legacy QA email action remains intentionally disabled.\n";

        if (!$emailsSent) echo "No email required.\n";
        echo "Obsolete toolbox ToDos closed: {$closedCount}.\n";
        echo "Emails sent: {$emailsSent}.\n";

        return $emailsSent + $closedCount;
    }
}
