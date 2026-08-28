<?php

namespace App\Scheduled\Reports;

use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Illuminate\Support\Facades\Mail;

class MaintenanceAppointmentReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.maintenance_appointment',
            'name' => 'Maintenance without appointment',
            'category' => 'report',
            'description' => 'Emails a list of active maintenance requests that do not have a client appointment.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05'], // Monday
            'recipients' => 'Legacy notification group: site.maintenance.appointment; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find active maintenance requests without a client appointment and email the list when required.
     */
    public function handle(): int
    {
        $maintenanceRequests = SiteMaintenance::query()->where('status', 1)->whereNull('client_appointment')->orderBy('reported')->get();
        echo "Maintenance requests without a client appointment: {$maintenanceRequests->count()}\n";

        if ($maintenanceRequests->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        // This remains the legacy/default recipient source. The central mail
        // listener can append dashboard rules or replace this list entirely.
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.maintenance.appointment');

        Mail::send('emails/site/maintenance-appointment', ['data' => $maintenanceRequests], function ($message) use ($emailList) {
            $message->from('do-not-reply@safeworksite.com.au', 'Safe Worksite');
            if ($emailList) $message->to($emailList);
            $message->subject('Maintenance Requests Without Appointment');
        });

        echo "Maintenance Without Appointment report sent.\n";

        return 1;
    }
}
