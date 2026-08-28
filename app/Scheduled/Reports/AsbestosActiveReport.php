<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteAsbestosActiveReport;
use App\Models\Company\Company;
use App\Models\Site\SiteAsbestos;
use App\Scheduled\Contracts\ScheduledOperationHandler;

class AsbestosActiveReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.active_asbestos',
            'name' => 'Active asbestos',
            'category' => 'report',
            'description' => 'Emails a list of all active asbestos notifications.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: site.asbestos.active',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Find active asbestos notifications and email the report when required.
     */
    public function handle(): int
    {
        $asbestosNotifications = SiteAsbestos::query()->where('status', 1)->orderBy('created_at')->get();
        echo "Active asbestos notifications: {$asbestosNotifications->count()}\n";

        if ($asbestosNotifications->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        // Use the report's existing notification group. If Append or Managed is
        // selected in the dashboard, those recipient changes are applied automatically.
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.asbestos.active');
        $mailable = new SiteAsbestosActiveReport($asbestosNotifications);
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo "Active Asbestos report sent.\n";

        return 1;
    }
}
