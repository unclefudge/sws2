<?php

namespace App\Scheduled\Reports;

use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class MaintenanceNoActionReport implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.maintenance_no_action',
            'name' => 'Maintenance no action',
            'category' => 'report',
            'description' => 'Emails active maintenance requests that have had no update or new action for 14 days.',
            'schedule' => ['type' => 'fortnightly', 'weekday' => 1, 'time' => '00:05', 'anchor' => '2020-10-26'], // Monday
            'recipients' => 'Legacy notification group: site.maintenance.noaction; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $inactiveCutoff = Carbon::now()->subDays(14);
        $maintenanceRequests = SiteMaintenance::query()->where('status', 1)->orderBy('reported')->get();

        // Cache lastUpdated(), retain every matching request, and sort by its
        // last activity. The legacy date-keyed array could overwrite requests
        // when several had their last action on the same day.
        $staleRequests = $maintenanceRequests->map(function (SiteMaintenance $request) {
            return ['request' => $request, 'last_updated' => $request->lastUpdated()];
        })->filter(fn($item) => $item['last_updated']->lt($inactiveCutoff))->sortBy('last_updated')->pluck('request')->values();

        echo 'Maintenance requests without action for 14 days: ' . $staleRequests->count() . "\n";

        if ($staleRequests->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.maintenance.noaction');
        Mail::send('emails/site/maintenance-noaction', ['data' => $staleRequests], function ($message) use ($emailList) {
            $message->from('do-not-reply@safeworksite.com.au', 'Safe Worksite');
            if ($emailList) $message->to($emailList);
            $message->subject('Maintenance Requests No Action');
        });

        echo "Maintenance No Action report sent.\n";

        return 1;
    }
}
