<?php

namespace App\Scheduled\Reports;

use App\Mail\Company\CompanyDocsPending;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Scheduled\Contracts\ScheduledOperationHandler;

class CompanyDocsPendingReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.company_docs_pending',
            'name' => 'Pending company documents',
            'category' => 'report',
            'description' => 'Emails a list of company documents currently awaiting approval.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: company.doc.pending',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find company documents awaiting approval and email the report.
     */
    public function handle(): int
    {
        $pendingDocuments = CompanyDoc::query()->where('status', 3)->where('company_id', 3)->orderBy('for_company_id')->get();
        echo "Pending company documents: {$pendingDocuments->count()}\n";

        // Use the report's existing notification group. If Append or Managed is
        // selected in the dashboard, those recipient changes are applied automatically.
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('company.doc.pending');
        $mailable = new CompanyDocsPending($pendingDocuments);
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo "Pending Company Documents report sent.\n";

        return 1;
    }
}
