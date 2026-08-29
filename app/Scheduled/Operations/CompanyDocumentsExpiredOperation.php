<?php

namespace App\Scheduled\Operations;

use App\Mail\Company\CompanyDocExpired;
use App\Models\Company\CompanyDoc;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientContext;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\User;
use Carbon\Carbon;

class CompanyDocumentsExpiredOperation implements ScheduledOperationHandler
{
    private const STANDARD_DETAILS_CATEGORY_ID = 22;
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
            'key' => 'nightly.expired_company_docs',
            'name' => 'Process expired company documents',
            'category' => 'documents',
            'description' => 'Expires overdue company documents and manages renewal reminders, company ToDos and approval notifications at the configured milestones.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Affected company Senior Users and the parent company document-approval notification groups',
            'dynamicRecipients' => [
                ['key' => 'company_senior_users', 'label' => 'Affected company Senior Users', 'delivery' => 'to', 'description' => 'Active Senior Users belonging to the company whose document is expiring.', 'required' => true],
                ['key' => 'document_approval_group', 'label' => 'Parent company document approval group', 'delivery' => 'cc', 'description' => 'The configured ACC or WHS approval notification group for the company that owns the document.', 'required' => false],
            ],
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $today = Carbon::today();
        $milestones = [
            $today->copy()->addDays(28)->toDateString() => 'Expiry in 4 weeks',
            $today->copy()->addDays(14)->toDateString() => 'Expiry in 2 weeks',
            $today->toDateString() => 'Expired today',
            $today->copy()->subDays(7)->toDateString() => 'Expired 1 week ago',
            $today->copy()->subDays(14)->toDateString() => 'Expired 2 weeks ago',
            $today->copy()->subDays(21)->toDateString() => 'Expired 3 weeks ago',
            $today->copy()->subDays(28)->toDateString() => 'Expired 4 weeks ago',
        ];
        $expiredCount = 0;
        $todoCount = 0;
        $emailsTriggered = 0;

        // Standard Details follow their own renewal cycle and deliberately
        // remain active here even after their expiry date has passed.
        $expiredDocs = CompanyDoc::query()->with(['category', 'company'])->where('status', 1)->whereDate('expiry', '<', $today)->orderBy('expiry')->get();
        foreach ($expiredDocs as $doc) {
            if ($this->isStandardDetails($doc)) continue;

            $doc->status = 0;
            $doc->updated_by = self::SYSTEM_USER_ID;
            $doc->save();
            $expiredCount++;
            $companyName = $doc->company?->name_alias ?? "Deleted company #{$doc->for_company_id}";
            echo "Expired company document [{$doc->id}] {$companyName} ({$doc->name}); expired {$doc->expiry->format('d/m/Y')}.\n";
        }

        $milestoneDocs = CompanyDoc::query()->with(['category', 'company'])->where(function ($query) use ($milestones) {
            foreach (array_keys($milestones) as $index => $date) {
                if ($index === 0) $query->whereDate('expiry', $date);
                else $query->orWhereDate('expiry', $date);
            }
        })->orderBy('expiry')->orderBy('for_company_id')->get()->groupBy(fn(CompanyDoc $doc) => $doc->expiry->format('Y-m-d'));
        $systemUser = User::findOrFail(self::SYSTEM_USER_ID);
        $twoWeeksAgo = $today->copy()->subDays(14)->toDateString();

        foreach ($milestones as $date => $label) {
            $docs = $milestoneDocs->get($date, collect());
            echo "{$label} on " . Carbon::parse($date)->format('d/m/Y') . ": {$docs->count()} document(s).\n";

            foreach ($docs as $doc) {
                $company = $doc->company;
                if (!$company || !$company->status) {
                    echo "Skipped company document [{$doc->id}]: its company is missing or inactive.\n";
                    continue;
                }

                echo "Processing company document [{$doc->id}] {$company->name_alias} ({$doc->name}).\n";
                if ($this->isStandardDetails($doc)) {
                    echo "Skipped Standard Details document [{$doc->id}]; it is handled by its separate renewal workflow.\n";
                    continue;
                }

                if ($doc->status) {
                    // Active ACC/WHS documents receive the existing advance
                    // and same-day renewal email through their model workflow.
                    if (in_array($doc->category?->type, ['acc', 'whs'], true)) {
                        $this->sendExpiredEmail($doc);
                        $emailsTriggered++;
                        echo "Triggered renewal email for active document [{$doc->id}].\n";
                    }
                    continue;
                }

                $doc->closeToDo($systemUser);
                if ($company->activeCompanyDoc($doc->category_id)) {
                    echo "No new ToDo required for [{$doc->id}]: a replacement document is active.\n";
                    continue;
                }

                $seniorUserIds = $company->seniorUsers()->pluck('id')->all();
                if ($seniorUserIds && (int)$company->id !== 3) {
                    $dynamicRecipients = $this->recipientResolver->users(
                        'company_senior_users',
                        'Affected company Senior Users',
                        $company->seniorUsers,
                        'to'
                    );
                    $this->recipientContext->run(
                        $dynamicRecipients,
                        fn() => $doc->createExpiredToDo($seniorUserIds, false)
                    );
                    $todoCount++;
                    echo "Created or refreshed the expired-document ToDo for [{$doc->id}].\n";
                }

                if ($date === $twoWeeksAgo && in_array($doc->category?->type, ['acc', 'whs'], true)) {
                    $this->sendExpiredEmail($doc);
                    $emailsTriggered++;
                    echo "Triggered the two-week overdue approval email for [{$doc->id}].\n";
                }
            }
        }

        echo "Company documents expired: {$expiredCount}; ToDo actions: {$todoCount}; email workflows triggered: {$emailsTriggered}.\n";

        return $expiredCount + $todoCount + $emailsTriggered;
    }

    private function isStandardDetails(CompanyDoc $doc): bool
    {
        return (int)$doc->category_id === self::STANDARD_DETAILS_CATEGORY_ID || (int)$doc->category?->parent === self::STANDARD_DETAILS_CATEGORY_ID;
    }

    private function documentRecipients(CompanyDoc $doc): array
    {
        $approvalEmails = $doc->owned_by?->notificationsUsersEmailType('doc.' . $doc->category?->type . '.approval') ?: [];

        return array_merge(
            $this->recipientResolver->users(
                'company_senior_users',
                'Affected company Senior Users',
                $doc->company?->seniorUsers ?? collect(),
                'to'
            ),
            $this->recipientResolver->emails(
                'document_approval_group',
                'Parent company document approval group',
                $approvalEmails,
                'cc',
                false
            )
        );
    }

    private function sendExpiredEmail(CompanyDoc $doc): void
    {
        $this->mailer->send(
            new CompanyDocExpired($doc),
            $this->documentRecipients($doc)
        );
    }
}
