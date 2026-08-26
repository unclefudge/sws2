<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteMaintenanceUnderReviewReport;
use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Sends the Maintenance Under Review PDF report.
 *
 * The operation key deliberately matches the existing CronReportController
 * entry. ScheduledOperationRegistry will therefore use this class for the v2
 * scheduler without creating a second copy of the report in the dashboard.
 */
class MaintenanceUnderReviewReport implements ScheduledOperationHandler
{
    /**
     * Safe defaults used when the handler is first discovered.
     *
     * After scheduled:sync has installed/refreshed this handler, its enabled
     * state, schedule and recipient mode are controlled from the web interface.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.maintenance_under_review',
            'name' => 'Maintenance under review',
            'category' => 'report',
            'description' => 'Emails a PDF containing all maintenance requests currently under review.',
            'schedule' => [
                'type' => 'weekly',
                'weekdays' => [1], // Monday. The dashboard can change this later.
                'time' => '00:05',
            ],
            'recipients' => 'Notification group: site.maintenance.underreview',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Build the report and send it when there is something to report.
     *
     * Progress is written with echo because RunScheduledOperation captures this
     * output and displays it against the individual run in the dashboard.
     */
    public function handle(): int
    {
        $today = Carbon::now();
        $maintenanceRequests = SiteMaintenance::query()->where('status', 2)->orderBy('reported')->get();
        $requestCount = $maintenanceRequests->count();
        echo "Maintenance requests under review: {$requestCount}\n";

        // No Maintenance Requests are Under Review is a successful run, but there is no useful email to
        // send.
        if ($maintenanceRequests->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $capeCod = Company::findOrFail(3);

        // Keep the current notification group as the legacy recipient source.
        // If the operation is changed to Append or Managed in the dashboard,
        // ApplyScheduledRecipientRules adjusts the final To/CC/BCC addresses at
        // MessageSending time without this report needing recipient logic edits.
        $emailList = app()->environment('prod')
            ? $capeCod->notificationsUsersEmailType('site.maintenance.underreview')
            : [config('mail.email_dev')];

        // A unique filename prevents a manual run and a queued run from writing
        // over one another. The file only needs to exist until send() completes.
        $directory = storage_path('app/tmp');
        File::ensureDirectoryExists($directory);
        $file = $directory . '/maintenance-under-review-'
            . $today->format('Ymd-His-u') . '.pdf';

        try {
            \PDF::loadView('pdf/site/maintenance-under-review', [
                'mains' => $maintenanceRequests,
                'today' => $today,
            ])->setPaper('a4', 'portrait')->save($file);

            // This existing mailable implements ShouldQueue. Sending it through
            // PendingMail would queue a second job, then this handler's finally
            // block would delete the PDF before that job could attach it. The
            // report is already inside its scheduled queue job, so call the
            // mailable's own send method to build, attach and transport it now.
            // Normal MessageSending/MessageSent events still fire, which keeps
            // the Scheduled Operations email audit and recipient rules working.
            (new SiteMaintenanceUnderReviewReport($file, $maintenanceRequests))
                ->to($emailList)
                ->send(app('mailer'));

            // The scheduler's email history records the final recipients after
            // any dashboard recipient rules have been applied.
            echo "Maintenance Under Review report sent.\n";
        } finally {
            // Do not retain generated PDFs after the synchronous mail send. The
            // v2 email audit stores metadata, not the potentially large PDF.
            File::delete($file);
        }

        return 1;
    }
}
