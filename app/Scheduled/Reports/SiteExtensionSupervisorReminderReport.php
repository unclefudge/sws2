<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteExtensionsReminder;
use App\Models\Site\SiteExtension;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\User;
use RuntimeException;

class SiteExtensionSupervisorReminderReport implements ScheduledOperationHandler
{
    public function __construct(private ScheduledDynamicRecipientResolver $recipientResolver, private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.extension_reminder',
            'name' => 'Site extension Supervisor reminder',
            'category' => 'report',
            'description' => 'Emails each affected Site Supervisor a Thursday reminder listing their outstanding Contract Time Extensions.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [4], 'time' => '00:05'], // Thursday
            'recipients' => 'Relevant Site Supervisor (To) plus dashboard-configurable management To/CC recipients',
            'dynamicRecipients' => [
                ['key' => 'site_supervisor', 'label' => 'Relevant Site Supervisor', 'delivery' => 'to', 'description' => 'The Supervisor responsible for the outstanding sites in the individual reminder.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $extension = SiteExtension::query()->with('sites.site')->where('status', 1)->latest('date')->first();
        if (!$extension) throw new RuntimeException('No active Contract Time Extension week exists.');

        $groups = $extension->sites->filter(fn($extensionSite) => !$extensionSite->reasons && $extensionSite->site?->supervisor_id)
            ->groupBy(fn($extensionSite) => (int) $extensionSite->site->supervisor_id);
        $emailsSent = 0;

        foreach ($groups as $supervisorId => $extensionSites) {
            if (!$extension->sitesNotCompletedBySupervisor($supervisorId)->count()) continue;

            $supervisor = User::find((int) $supervisorId);
            $siteList = $extensionSites->pluck('site')->filter()->sortBy('name')->map(fn($site) => "- {$site->name}")->implode("\n");
            $mailable = new SiteExtensionsReminder($extension, $siteList);
            $dynamicRecipients = $this->recipientResolver->user('site_supervisor', 'Relevant Site Supervisor', $supervisor, 'to');
            $hasSupervisorEmail = collect($dynamicRecipients)->contains(fn(array $recipient) => !empty($recipient['email']));

            // Retain Kirstie as the legacy management CC. If the Supervisor no
            // longer has a valid email, use management as the legacy fallback.
            if ($hasSupervisorEmail) $mailable->cc(['kirstie@capecod.com.au']);
            else $mailable->to(['kirstie@capecod.com.au']);

            $this->mailer->send($mailable, $dynamicRecipients);
            $emailsSent++;
            echo "Sent extension reminder for " . ($supervisor?->fullname ?: "missing Supervisor #{$supervisorId}") . ': ' . $extensionSites->count() . " outstanding site(s).\n";
        }

        if (!$emailsSent) echo "No Supervisor extension reminder was required.\n";

        return $emailsSent;
    }
}
