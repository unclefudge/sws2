<?php

namespace App\Scheduled\Reports;

use App\Mail\Company\CompanyUploadDocsReminder;
use App\Models\Company\Company;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use Carbon\Carbon;

class CompanyDocumentUploadReminderReport implements ScheduledOperationHandler
{
    private const REPORTING_COMPANY_ID = 3;
    private const LEGACY_MANAGEMENT_CC = ['kirstie@capecod.com.au', 'accounts1@capecod.com.au'];

    public function __construct(private ScheduledDynamicRecipientResolver $recipientResolver, private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.company_doc_reminders',
            'name' => 'Company document upload reminders',
            'category' => 'report',
            'description' => 'Emails newly added non-compliant companies a reminder to upload their missing required documents.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'Affected company contact (To) plus dashboard-configurable management To/CC recipients',
            'dynamicRecipients' => [
                ['key' => 'company_contact', 'label' => 'Affected company contact', 'delivery' => 'to', 'description' => 'The primary or secondary contact for the individual company, falling back to its company email address.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $companies = Company::query()->whereDate('created_at', Carbon::yesterday()->toDateString())->orderBy('name')->get();
        $eligibleCount = 0;
        $emailsSent = 0;

        echo "Companies created yesterday: {$companies->count()}.\n";

        foreach ($companies as $company) {
            if ($company->isCompliant() || (int)$company->reportsTo()?->id !== self::REPORTING_COMPANY_ID) continue;

            $eligibleCount++;
            $missingDocuments = $company->missingDocs('csv');
            $dynamicRecipients = $this->recipientResolver->companyContact('company_contact', 'Affected company contact', $company, 'to');
            $resolvedContact = collect($dynamicRecipients)->first(fn(array $recipient) => !empty($recipient['email']));
            $mailable = new CompanyUploadDocsReminder($company);

            // Legacy and Append retain the existing management CC addresses.
            // Managed mode replaces them with the To/CC rules configured on
            // the scheduler while always retaining the dynamic company contact.
            $mailable->cc(self::LEGACY_MANAGEMENT_CC);
            $this->mailer->send($mailable, $dynamicRecipients);
            $emailsSent++;

            $contact = $resolvedContact['email'] ?? 'no valid company contact; management fallback used';
            echo "Sent company document reminder for [{$company->id}] {$company->name} to {$contact}. Missing documents: {$missingDocuments}.\n";
        }

        if (!$eligibleCount) echo "No newly added non-compliant companies required a document reminder.\n";
        echo "Eligible companies: {$eligibleCount}; reminder emails sent: {$emailsSent}.\n";

        return $emailsSent;
    }
}
