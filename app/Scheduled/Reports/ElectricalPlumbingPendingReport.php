<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteInspectionPending;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Scheduled\Contracts\ScheduledOperationHandler;

class ElectricalPlumbingPendingReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.pending_electrical_plumbing',
            'name' => 'Pending electrical and plumbing',
            'category' => 'report',
            'description' => 'Emails pending electrical and plumbing inspections grouped by their outstanding signature or notification stage.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: site.inspection.pending',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Build the electrical and plumbing inspection categories and email the report.
     */
    public function handle(): int
    {
        $includedSiteIds = Site::query()->whereIn('status', [1, 2, -1])->pluck('id');
        $electrical = SiteInspectionElectrical::query()->where('status', 3)->whereIn('site_id', $includedSiteIds)->get();
        $plumbing = SiteInspectionPlumbing::query()->where('status', 3)->whereIn('site_id', $includedSiteIds)->get();

        // These groups intentionally overlap, matching the six legacy queries.
        $electricalPendingAdmin = $electrical->filter(fn(SiteInspectionElectrical $inspection) => $inspection->supervisor_sign_by === null)->values();
        $electricalPendingTech = $electrical->filter(fn(SiteInspectionElectrical $inspection) => $inspection->manager_sign_by === null)->values();
        $electricalClientNotification = $electrical->filter(fn(SiteInspectionElectrical $inspection) => $inspection->manager_sign_by !== null)->values();
        $plumbingPendingAdmin = $plumbing->filter(fn(SiteInspectionPlumbing $inspection) => $inspection->supervisor_sign_by === null)->values();
        $plumbingPendingTech = $plumbing->filter(fn(SiteInspectionPlumbing $inspection) => $inspection->manager_sign_by === null)->values();
        $plumbingClientNotification = $plumbing->filter(fn(SiteInspectionPlumbing $inspection) => $inspection->manager_sign_by !== null)->values();

        echo "Electrical inspections: {$electrical->count()}\n";
        echo "Plumbing inspections: {$plumbing->count()}\n";

        // The legacy report sent all six sections even when they were empty.
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.inspection.pending');
        $mailable = new SiteInspectionPending(
            $electricalPendingAdmin,
            $electricalPendingTech,
            $electricalClientNotification,
            $plumbingPendingAdmin,
            $plumbingPendingTech,
            $plumbingClientNotification
        );
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo "Pending Electrical and Plumbing report sent.\n";

        return 1;
    }
}
