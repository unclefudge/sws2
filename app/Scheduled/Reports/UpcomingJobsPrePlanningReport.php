<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteUpcomingJobs;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledReportMailer;
use App\Scheduled\Support\UpcomingJobsReportBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class UpcomingJobsPrePlanningReport implements ScheduledOperationHandler
{
    public function __construct(private UpcomingJobsReportBuilder $builder, private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'hourly.upcoming_jobs_monday',
            'name' => 'Upcoming jobs email (Monday)',
            'category' => 'report',
            'description' => 'Sends the Upcoming Jobs Compliance PDF before the Monday planning meeting.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '13:01'],
            'recipients' => 'Recipients configured in Scheduled Operations',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $file = $this->builder->createPdf();
        $subject = 'Upcoming Jobs Compliance - Pre Planning Meeting ' . Carbon::now()->format('d.m.y');

        try {
            $mailable = new SiteUpcomingJobs($file, $subject);
            $this->mailer->send($mailable);
            echo "Monday Upcoming Jobs report sent.\n";
        } finally {
            File::delete($file);
        }

        return 1;
    }
}
