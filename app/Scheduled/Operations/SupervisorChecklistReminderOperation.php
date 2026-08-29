<?php

namespace App\Scheduled\Operations;

use App\Models\Comms\Todo;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Scheduled\ScheduledReportMessage;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledRunContext;
use Illuminate\Support\Str;
use Throwable;

class SupervisorChecklistReminderOperation implements ScheduledOperationHandler
{
    public function __construct(
        private ScheduledDynamicRecipientResolver $recipientResolver,
        private ScheduledDynamicRecipientContext  $recipientContext,
        private ScheduledRunContext               $runContext
    )
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'hourly.super_checklist_reminder',
            'name' => 'Supervisor checklist reminder',
            'category' => 'notifications',
            'description' => 'Emails the relevant Supervisor about each outstanding Supervisor checklist.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1, 2, 3, 4, 5], 'time' => '14:01'],
            'recipients' => 'Relevant checklist Supervisor; dashboard recipients can append management recipients or provide a fallback',
            'dynamicRecipients' => [
                ['key' => 'checklist_supervisor', 'label' => 'Checklist Supervisor', 'delivery' => 'to', 'description' => 'The Supervisor assigned to the outstanding checklist.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $todos = Todo::query()->where('type', 'super checklist')->where('status', 1)->orderBy('id')->get(['id', 'type_id']);
        $checklists = SuperChecklist::query()->with('supervisor')->whereIn('id', $todos->pluck('type_id')->filter()->unique())->get()->keyBy('id');
        $sent = 0;
        $missing = 0;

        foreach ($todos as $todo) {
            $checklist = $checklists->get($todo->type_id);

            if (!$checklist) {
                $missing++;
                echo "Checklist not found for ToDo {$todo->id}.\n";
                continue;
            }

            $dynamicRecipients = $this->recipientResolver->user('checklist_supervisor', 'Checklist Supervisor', $checklist->supervisor, 'to');

            try {
                // The existing model method builds the original reminder email.
                // The dynamic context lets Managed mode retain the record-specific
                // Supervisor while applying any management To/CC rules from the UI.
                $this->recipientContext->run($dynamicRecipients, fn() => $checklist->emailSupervisorReminder());
                $sent++;
                echo 'Supervisor checklist reminder sent for checklist ' . $checklist->id . ".\n";
            } catch (Throwable $exception) {
                $this->recordFailure($checklist->id, $exception);
                echo 'Supervisor checklist reminder failed for checklist ' . $checklist->id . ': ' . $exception->getMessage() . "\n";
            }
        }

        echo "Outstanding checklist ToDos: {$todos->count()}\n";
        echo "Reminder emails sent: {$sent}\n";
        echo "Missing checklists: {$missing}\n";

        return $sent;
    }

    private function recordFailure(int $checklistId, Throwable $exception): void
    {
        $runId = $this->runContext->runId();
        if (!$runId) return;

        $audit = ScheduledReportMessage::query()->where('scheduled_run_id', $runId)->where('status', 'sending')->latest('id')->first();
        $values = ['status' => 'failed', 'failed_at' => now(), 'error' => $exception->getMessage()];

        if ($audit) {
            $audit->update($values);
            return;
        }

        ScheduledReportMessage::create($values + [
                'scheduled_run_id' => $runId,
                'uuid' => (string)Str::uuid(),
                'subject' => "Supervisor checklist reminder {$checklistId}",
                'attachments' => [],
            ]);
    }
}
