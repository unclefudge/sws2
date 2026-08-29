<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteUpcomingJobs;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledReportMailer;
use App\Scheduled\Support\UpcomingJobsReportBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class UpcomingJobsPostPlanningReport implements ScheduledOperationHandler
{
    public function __construct(private UpcomingJobsReportBuilder $builder, private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'hourly.upcoming_jobs_thursday',
            'name' => 'Upcoming jobs email (Thursday)',
            'category' => 'report',
            'description' => 'Sends the Upcoming Jobs Compliance PDF after the Thursday planning meeting.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [4], 'time' => '10:01'],
            'recipients' => 'Legacy planning team To/CC lists; dashboard recipients can append to or replace them',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $file = $this->builder->createPdf();
        $subject = 'Upcoming Jobs Compliance - Post Planning Meeting ' . Carbon::now()->format('d.m.y');

        try {
            $mailable = new SiteUpcomingJobs($file, $subject);
            $mailable->to(['alethea@capecod.com.au', 'keith@capecod.com.au', 'kirstie@capecod.com.au', 'nadia@capecod.com.au', 'ross@capecod.com.au']);
            $mailable->cc(['clinton@capecod.com.au', 'scott@capecod.com.au', 'michelle@capecod.com.au', 'jayden@capecod.com.au']);
            $this->mailer->send($mailable);
            echo "Thursday Upcoming Jobs report sent.\n";
        } finally {
            File::delete($file);
        }

        return 1;
    }
}
