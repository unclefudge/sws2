<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteFocDefectiveReport;
use App\Models\Company\Company;
use App\Models\Misc\Category;
use App\Models\Scheduled\ScheduledReportMessage;
use App\Models\Site\SiteFocItem;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\Scheduled\ScheduledRunContext;
use Illuminate\Support\Str;
use Throwable;

class FocDefectiveInspectionsReport implements ScheduledOperationHandler
{
    private const COMPANY_ID = 3;

    public function __construct(
        private ScheduledDynamicRecipientResolver $recipientResolver,
        private ScheduledReportMailer             $mailer,
        private ScheduledRunContext               $runContext
    )
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.foc_defective',
            'name' => 'FOC defective inspections',
            'category' => 'report',
            'description' => 'Emails each relevant Supervisor a grouped list of their outstanding defective FOC inspection items.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05'], // Monday
            'recipients' => 'Site Supervisor (To) plus dashboard-configurable management To/CC recipients',
            'dynamicRecipients' => [
                ['key' => 'site_supervisor', 'label' => 'Site Supervisor', 'delivery' => 'to', 'description' => 'The Supervisor responsible for the FOC inspections included in each individual email.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $defectiveCategory = Category::query()->where('type', 'foc_item')->where('name', 'Defective')->where('status', 1)->first();
        if (!$defectiveCategory) {
            echo "No active FOC Defective category was found; no report was sent.\n";
            return 0;
        }

        $items = SiteFocItem::query()->with(['foc.site', 'foc.supervisor'])->where('category_id', $defectiveCategory->id)->where('status', 1)
            ->whereHas('foc', fn($query) => $query->where('status', '<>', -1))->orderBy('order')->get()
            ->filter(fn($item) => $item->foc && $item->foc->super_id && $item->foc->site);
        if ($items->isEmpty()) {
            echo "No outstanding defective FOC inspection items were found.\n";
            return 0;
        }

        $legacyManagementEmails = Company::findOrFail(self::COMPANY_ID)->notificationsUsersEmailType('site.foc.defective');
        $sentCount = 0;
        $failedCount = 0;

        echo "Outstanding defective FOC items: {$items->count()}; Supervisor groups: " . $items->groupBy(fn($item) => (int)$item->foc->super_id)->count() . ".\n";

        foreach ($items->groupBy(fn($item) => (int)$item->foc->super_id) as $supervisorId => $supervisorItems) {
            $supervisor = $supervisorItems->first()->foc->supervisor;
            $supervisorName = $supervisor?->name ?: "Missing Supervisor #{$supervisorId}";
            $supervisorFirstName = $supervisor?->firstname ?: $supervisorName;
            $jobs = $this->jobs($supervisorItems);
            $dynamicRecipients = $this->recipientResolver->user('site_supervisor', 'Relevant Site Supervisor', $supervisor, 'to');
            $hasSupervisorEmail = collect($dynamicRecipients)->contains(fn(array $recipient) => !empty($recipient['email']));
            $mailable = new SiteFocDefectiveReport(supervisorName: $supervisorName, supervisorFirstName: $supervisorFirstName, jobs: $jobs);

            // Legacy and Append retain the existing management notification
            // group. Managed mode replaces it with dashboard To/CC rules while
            // always retaining the valid dynamic Supervisor recipient.
            if ($hasSupervisorEmail && $legacyManagementEmails) $mailable->cc(array_values(array_diff($legacyManagementEmails, [$supervisor->email])));
            elseif (!$hasSupervisorEmail && $legacyManagementEmails) $mailable->to($legacyManagementEmails);

            try {
                // Always submit the mailable: Managed recipients must still be
                // able to receive it when the old notification group is empty.
                $this->mailer->send($mailable, $dynamicRecipients);
                $sentCount++;
                echo "Sent FOC defective report for {$supervisorName}: " . count($jobs) . " site(s), {$supervisorItems->count()} defect(s).\n";
            } catch (Throwable $exception) {
                $failedCount++;
                $this->recordFailedMessage($supervisorName, $exception);
                echo "Failed FOC defective report for {$supervisorName}: {$exception->getMessage()}. Remaining Supervisors will still be processed.\n";
            }
        }

        echo "FOC defective reports sent: {$sentCount}; failed: {$failedCount}.\n";

        return $sentCount;
    }

    private function jobs($supervisorItems): array
    {
        return $supervisorItems->groupBy(fn($item) => $item->foc_id)->map(function ($focItems) {
            $first = $focItems->first();
            $site = $first->foc->site;

            return [
                'foc_id' => $first->foc_id,
                'site_code' => $site->code,
                'site_name' => $site->name,
                'defects' => $focItems->map(fn($item) => ['name' => $item->name, 'updated_at' => $item->updated_at?->format('d/m/Y') ?? '-'])->values()->all(),
            ];
        })->sortBy('site_code')->values()->all();
    }

    private function recordFailedMessage(string $supervisorName, Throwable $exception): void
    {
        $runId = $this->runContext->runId();
        if (!$runId) return;

        $message = ScheduledReportMessage::query()->where('scheduled_run_id', $runId)->where('status', 'sending')->latest('id')->first();
        $values = ['status' => 'failed', 'failed_at' => now(), 'error' => $exception->getMessage()];

        if ($message) {
            $message->update($values);
            return;
        }

        // Recipient-rule failures can occur before MessageSending reaches the
        // audit listener. Create the failed row here so the outer run is still
        // marked failed without retrying emails already sent to other groups.
        ScheduledReportMessage::create($values + ['scheduled_run_id' => $runId, 'uuid' => (string)Str::uuid(), 'subject' => "FOC defective inspections - {$supervisorName}",]);
    }
}
