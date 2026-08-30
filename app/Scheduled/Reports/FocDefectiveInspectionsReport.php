<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteFocDefectiveReport;
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
    public function __construct(private ScheduledDynamicRecipientResolver $recipientResolver, private ScheduledReportMailer $mailer, private ScheduledRunContext $runContext)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.foc_defective',
            'name' => 'FOC defective inspections',
            'category' => 'report',
            'description' => 'Emails each relevant FOC Supervisor a grouped list of their outstanding defective FOC inspection items. Unassigned items are still sent to the configured recipients.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05'], // Monday
            'recipients' => 'FOC Supervisor (To, when assigned) plus dashboard-configurable management To/CC recipients',
            'dynamicRecipients' => [
                ['key' => 'foc_supervisor', 'label' => 'FOC Supervisor', 'delivery' => 'to', 'description' => 'The FOC Supervisor responsible for the inspections included in each individual email, when assigned.', 'required' => false],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $items = SiteFocItem::query()->with(['category', 'foc.site', 'foc.supervisor'])->where('status', SiteFocItem::STATUS_DEFECTIVE)
            ->whereHas('category', fn($query) => $query
                ->where('type', 'foc_item')
                ->where('status', 1)
                ->whereRaw('LOWER(name) = ?', ['inspections']))
            ->whereHas('foc', fn($query) => $query->where('status', '<>', -1))->orderBy('order')->get()
            ->filter(fn($item) => $item->foc && $item->foc->site);
        if ($items->isEmpty()) {
            echo "No outstanding defective FOC inspection items were found.\n";
            return 0;
        }

        $sentCount = 0;
        $failedCount = 0;

        $supervisorGroups = $items->groupBy(fn($item) => $item->foc->super_id ?: 'unassigned');

        echo "Outstanding defective FOC items: {$items->count()}; FOC Supervisor groups: {$supervisorGroups->count()}.\n";

        foreach ($supervisorGroups as $supervisorId => $supervisorItems) {
            $supervisor = $supervisorItems->first()->foc->supervisor;
            $supervisorName = $supervisor?->name ?: 'Unassigned FOC Supervisor';
            $supervisorFirstName = $supervisor?->firstname ?: 'there';
            $jobs = $this->jobs($supervisorItems);
            $dynamicRecipients = $supervisor ? $this->recipientResolver->user('foc_supervisor', 'Relevant FOC Supervisor', $supervisor, 'to') : [];
            $mailable = new SiteFocDefectiveReport(supervisorName: $supervisorName, supervisorFirstName: $supervisorFirstName, jobs: $jobs);

            try {
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
                'defects' => $focItems->map(fn($item) => ['name' => $item->name, 'notes' => $item->notes, 'updated_at' => $item->updated_at?->format('d/m/Y') ?? '-',])->values()->all(),
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

        // Recipient-rule failures can occur before MessageSending reaches the audit listener. Create the failed row here so the outer run is still
        // marked failed without retrying emails already sent to other groups.
        ScheduledReportMessage::create($values + ['scheduled_run_id' => $runId, 'uuid' => (string)Str::uuid(), 'subject' => "FOC defective inspections - {$supervisorName}",]);
    }
}
