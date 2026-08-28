<?php

namespace App\Scheduled\Reports;

use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Illuminate\Support\Facades\Mail;

class MaintenanceOnHoldReport implements ScheduledOperationHandler
{
    /** This was the second report inside the old combined fortnightly method. */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.maintenance_on_hold',
            'name' => 'Maintenance on hold',
            'category' => 'report',
            'description' => 'Emails a list of maintenance requests currently on hold.',
            'schedule' => ['type' => 'fortnightly', 'weekday' => 1, 'time' => '00:05', 'anchor' => '2020-10-26'], // Monday
            'recipients' => 'Legacy notification group: site.maintenance.onhold; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find maintenance requests currently on hold and email the list when required.
     */
    public function handle(): int
    {
        $maintenanceRequests = SiteMaintenance::query()->where('status', 4)->orderBy('reported')->get();
        echo 'Maintenance requests on hold: ' . $maintenanceRequests->count() . "\n";

        if ($maintenanceRequests->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.maintenance.onhold');

        Mail::send('emails/site/maintenance-onhold', ['data' => $maintenanceRequests], function ($message) use ($emailList) {
            $message->from('do-not-reply@safeworksite.com.au', 'Safe Worksite');
            if ($emailList) $message->to($emailList);
            $message->subject('Maintenance Requests On Hold');
        });

        echo "Maintenance On Hold report sent.\n";

        return 1;
    }
}
