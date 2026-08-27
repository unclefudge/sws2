<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteMaintenanceUnderReviewReport;
use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class MaintenanceUnderReviewReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
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
     */
    public function handle(): int
    {
        $today = Carbon::now();
        $maintenanceRequests = SiteMaintenance::query()->where('status', 2)->orderBy('reported')->get();
        $requestCount = $maintenanceRequests->count();

        echo "Maintenance requests under review: {$requestCount}\n";

        if ($maintenanceRequests->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $capeCod = Company::findOrFail(3);

        // Use the report's existing notification group. If Append or Managed is
        // selected in the dashboard, those recipient changes are applied automatically.
        $emailList = app()->environment('prod') ? $capeCod->notificationsUsersEmailType('site.maintenance.underreview') : [config('mail.email_dev')];
    
        // A unique filename prevents a manual and queued run from writing over one another.
        $directory = storage_path('app/tmp');
        File::ensureDirectoryExists($directory);
        $file = $directory . '/maintenance-under-review-' . $today->format('Ymd-His-u') . '.pdf';

        try {
            \PDF::loadView('pdf/site/maintenance-under-review', ['mains' => $maintenanceRequests, 'today' => $today,])->setPaper('a4', 'portrait')->save($file);

            // Send the email now instead of queuing it again inside the mailable's 'ShouldQueue'
            // This ensures the PDF still exists when it is attached
            (new SiteMaintenanceUnderReviewReport($file, $maintenanceRequests))->to($emailList)->send(app('mailer'));
            echo "Maintenance Under Review report sent.\n";
        } finally {
            // Do not retain generated PDFs. The scheduler stores metadata, not potentially large PDF.
            File::delete($file);
        }

        return 1;
    }
}
