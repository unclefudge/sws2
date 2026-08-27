<?php

namespace App\Scheduled\Reports;

use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Illuminate\Support\Facades\Mail;

class OutstandingAftercareReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.outstanding_aftercare',
            'name' => 'Outstanding aftercare',
            'category' => 'report',
            'description' => 'Emails a list of maintenance requests without an aftercare form.',
            'schedule' => [
                'type' => 'monthly_last_weekday',
                'weekday' => 5, // Last Friday. The dashboard can change this later.
                'time' => '00:05',
            ],
            'recipients' => 'Notification group: site.maintenance.aftercare',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find maintenance requests without an aftercare form and email the list when required.
     */
    public function handle(): int
    {
        $maintenanceRequests = SiteMaintenance::query()->where('status', 0)->whereNull('ac_form_sent')->orderBy('updated_at')->get();
        echo "Outstanding aftercare requests: {$maintenanceRequests->count()}\n";

        if ($maintenanceRequests->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $capeCod = Company::findOrFail(3);

        // Use the report's existing notification group. If Append or Managed is
        // selected in the dashboard, those recipient changes are applied automatically.
        $emailList = app()->environment('prod') ? $capeCod->notificationsUsersEmailType('site.maintenance.aftercare') : [config('mail.email_dev')];

        Mail::send('emails/site/maintenance-aftercare', ['data' => $maintenanceRequests], function ($message) use ($emailList) {
            $message->from('do-not-reply@safeworksite.com.au', 'Safe Worksite');
            if ($emailList) $message->to($emailList);
            $message->subject('Maintenance Requests Without After Care');
        });

        echo "Outstanding Aftercare report sent.\n";

        return 1;
    }
}
