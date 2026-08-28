<?php

namespace App\Scheduled\Reports;

use App\Http\Controllers\Site\SiteUpcomingComplianceController;
use App\Models\Company\Company;
use App\Models\Site\SiteUpcomingSettings;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class UpcomingJobComplianceReport implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.upcoming_job_compliance',
            'name' => 'Upcoming job compliance',
            'category' => 'report',
            'description' => 'Emails the upcoming jobs compliance data and attaches a landscape PDF copy.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [2], 'time' => '00:05'], // Tuesday
            'recipients' => 'Legacy notification group: site.upcoming.compliance; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $types = ['opt', 'cfest', 'cfadm'];
        $settingsColours = array_fill_keys($types, []);
        $settings = SiteUpcomingSettings::query()->whereIn('field', $types)->where('status', 1)->get(['field', 'order', 'colour']);

        // Only the colours are used by the PDF/email. Loading all three setting
        // types together replaces the legacy nine near-identical queries.
        foreach ($settings as $setting) {
            $parts = explode('-', (string)$setting->colour);
            $hex = $parts[2] ?? '';
            $settingsColours[$setting->field][$setting->order] = $hex !== '' ? "#{$hex}" : '';
        }

        $startdata = SiteUpcomingComplianceController::getUpcomingData();
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.upcoming.compliance');
        $directory = storage_path('app/tmp');
        File::ensureDirectoryExists($directory);
        $file = $directory . '/upcoming-jobs-' . Carbon::now()->format('Ymd-His-u') . '.pdf';

        try {
            \PDF::loadView('pdf/site/upcoming-compliance', ['startdata' => $startdata, 'settings_colours' => $settingsColours])->setPaper('A4', 'landscape')->save($file);

            Mail::send('emails/site/upcoming-compliance', ['startdata' => $startdata, 'settings_colours' => $settingsColours], function ($message) use ($emailList, $file) {
                $message->from('do-not-reply@safeworksite.com.au', 'Safe Worksite');
                if ($emailList) $message->to($emailList);
                $message->subject('SafeWorksite - Upcoming Jobs Compliance Data');
                $message->attach($file);
            });

            echo "Upcoming Job Compliance report sent.\n";
        } finally {
            File::delete($file);
        }

        return 1;
    }
}
