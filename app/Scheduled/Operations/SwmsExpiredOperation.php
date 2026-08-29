<?php

namespace App\Scheduled\Operations;

use App\Mail\Safety\WmsExpired;
use App\Models\Company\Company;
use App\Models\Safety\WmsDoc;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\User;
use Carbon\Carbon;

class SwmsExpiredOperation implements ScheduledOperationHandler
{
    private const VALID_YEARS = 2;
    private const SYSTEM_USER_ID = 1;

    public function __construct(
        private ScheduledDynamicRecipientResolver $recipientResolver,
        private ScheduledDynamicRecipientContext $recipientContext,
        private ScheduledReportMailer $mailer
    ) {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.expired_swms',
            'name' => 'Process expired SWMS',
            'category' => 'documents',
            'description' => 'Creates SWMS renewal ToDos and approval notifications two weeks before, on and four weeks after their two-year renewal date.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Affected company Senior Users and the parent company SWMS-approval notification group',
            'dynamicRecipients' => [
                ['key' => 'company_senior_users', 'label' => 'Affected company Senior Users', 'delivery' => 'to', 'description' => 'Active Senior Users belonging to the company whose SWMS is expiring.', 'required' => true],
                ['key' => 'swms_approval_group', 'label' => 'Parent company SWMS approval group', 'delivery' => 'cc', 'description' => 'The configured SWMS approval notification group for the parent company.', 'required' => false],
            ],
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $milestones = [
            $today->copy()->addDays(14)->subYears(self::VALID_YEARS)->toDateString() => ['label' => 'Expiry in 2 weeks', 'stage' => 'due'],
            $today->copy()->subYears(self::VALID_YEARS)->toDateString() => ['label' => 'Expired today', 'stage' => 'expired'],
            $today->copy()->subDays(28)->subYears(self::VALID_YEARS)->toDateString() => ['label' => 'Expired 4 weeks ago', 'stage' => 'overdue'],
        ];
        $docs = WmsDoc::query()->where('master', 0)->where('status', 1)->where(function ($query) use ($milestones) {
            foreach (array_keys($milestones) as $index => $date) {
                if ($index === 0) $query->whereDate('updated_at', $date);
                else $query->orWhereDate('updated_at', $date);
            }
        })->orderBy('updated_at')->orderBy('for_company_id')->get();
        $companies = Company::query()->whereIn('id', $docs->pluck('for_company_id')->unique())->get()->keyBy('id');
        $groupedDocs = $docs->groupBy(fn(WmsDoc $doc) => $doc->updated_at->format('Y-m-d'));
        $systemUser = User::findOrFail(self::SYSTEM_USER_ID);
        $todoCount = 0;
        $emailsTriggered = 0;

        echo "Active SWMS at two-year renewal milestones: {$docs->count()}.\n";

        foreach ($milestones as $date => $milestone) {
            $milestoneDocs = $groupedDocs->get($date, collect());
            echo "{$milestone['label']} on " . Carbon::parse($date)->addYears(self::VALID_YEARS)->format('d/m/Y') . ": {$milestoneDocs->count()} SWMS document(s).\n";

            foreach ($milestoneDocs as $doc) {
                $company = $companies->get($doc->for_company_id);
                if (!$company || !$company->status) {
                    echo "Skipped SWMS [{$doc->id}]: its company is missing or inactive.\n";
                    continue;
                }

                $seniorUserIds = $company->seniorUsers()->pluck('id')->all();
                $approvalRecipients = $company->reportsTo()?->notificationsUsersEmailType('swms.approval') ?: [];
                $dynamicRecipients = array_merge(
                    $this->recipientResolver->users('company_senior_users', 'Affected company Senior Users', $company->seniorUsers, 'to'),
                    $this->recipientResolver->emails('swms_approval_group', 'Parent company SWMS approval group', $approvalRecipients, 'cc', false)
                );
                echo "Processing SWMS [{$doc->id}] {$company->name_alias} ({$doc->name}); last updated {$doc->updated_at->format('d/m/Y')}.\n";

                if ($milestone['stage'] === 'due') {
                    if ($seniorUserIds && (int)$company->id !== 3) {
                        $this->recipientContext->run(
                            $this->recipientResolver->users('company_senior_users', 'Affected company Senior Users', $company->seniorUsers, 'to'),
                            fn() => $doc->createExpiredToDo($seniorUserIds, false)
                        );
                        $todoCount++;
                    }
                    $this->mailer->send(new WmsExpired($doc, false), $dynamicRecipients);
                    $emailsTriggered++;
                    echo "Triggered the two-week renewal ToDo and approval email for SWMS [{$doc->id}].\n";
                    continue;
                }

                $doc->closeToDo($systemUser);
                if ($seniorUserIds && (int)$company->id !== 3) {
                    $this->recipientContext->run(
                        $this->recipientResolver->users('company_senior_users', 'Affected company Senior Users', $company->seniorUsers, 'to'),
                        fn() => $doc->createExpiredToDo($seniorUserIds, true)
                    );
                    $todoCount++;
                }

                if ($milestone['stage'] === 'overdue') {
                    $this->mailer->send(new WmsExpired($doc, true), $dynamicRecipients);
                    $emailsTriggered++;
                    echo "Triggered the four-week overdue approval email for SWMS [{$doc->id}].\n";
                } else {
                    echo "Created or refreshed the expired SWMS ToDo for [{$doc->id}].\n";
                }
            }
        }

        echo "SWMS ToDo actions: {$todoCount}; approval email workflows triggered: {$emailsTriggered}.\n";

        return $todoCount + $emailsTriggered;
    }
}
