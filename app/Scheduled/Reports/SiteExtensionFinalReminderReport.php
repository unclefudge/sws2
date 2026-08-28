<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteExtensionsFinalReminder;
use App\Models\Site\SiteExtension;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledReportMailer;
use RuntimeException;

class SiteExtensionFinalReminderReport implements ScheduledOperationHandler
{
    public function __construct(private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.extension_final_reminder',
            'name' => 'Site extension final reminder',
            'category' => 'report',
            'description' => 'Emails management a Friday summary of outstanding Contract Time Extensions or requests final sign-off when all sites are complete.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [5], 'time' => '00:05'], // Friday
            'recipients' => 'Dashboard-configurable management To/CC recipients; legacy recipient is Kirstie',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $extension = SiteExtension::query()->with('sites.site')->where('status', 1)->latest('date')->first();
        if (!$extension) throw new RuntimeException('No active Contract Time Extension week exists.');

        $weekLabel = $extension->date->format('d/m/Y');
        if ($extension->approved_by) {
            echo "Extension week {$weekLabel} is already signed off. No reminder was required.\n";
            return 0;
        }

        $outstandingSites = $extension->sites->filter(fn($extensionSite) => !$extensionSite->reasons && $extensionSite->site)->sortBy(fn($extensionSite) => $extensionSite->site->name);
        $siteList = $outstandingSites->map(fn($extensionSite) => "- {$extensionSite->site->name} ({$extensionSite->site->supervisorName})")->implode("\n");

        if ($siteList) {
            $message = "Please ensure all Contract Time Extensions are completed for week of {$weekLabel} and Signed Off ASAP.<br><br>";
            $message .= "The following sites are yet to be completed:\n{$siteList}";
        } else {
            $message = "All Contract Time Extensions are completed for week of {$weekLabel}, please Sign Off ASAP.<br>";
        }

        $mailable = new SiteExtensionsFinalReminder($extension, $message);
        $mailable->to(['kirstie@capecod.com.au']);
        $this->mailer->send($mailable);
        echo "Sent final extension reminder for week {$weekLabel}; outstanding sites: " . $outstandingSites->count() . ".\n";

        return 1;
    }
}
