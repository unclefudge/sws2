<?php

namespace App\Scheduled\Reports;

use App\Jobs\SitePlannerPdf;
use App\Mail\Site\SiteSupervisorSiteExport;
use App\Models\Company\Company;
use App\Models\Misc\Report;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Services\SitePlannerDataBuilder;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupervisorSiteExportReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.supervisor_site_export',
            'name' => 'Supervisor site export',
            'category' => 'report',
            'description' => 'Generates one site-plan PDF per active Supervisor and emails all completed exports together.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: site.supervisor.export',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Create one PDF job per active Supervisor, then email the completed files
     * together after the batch finishes. Child jobs inherit the scheduled run
     * context, so their final email and failures remain attached to this run.
     */
    public function handle(): int
    {
        $capeCod = Company::findOrFail(3);
        $reportDate = Carbon::now()->subDay();
        $batchId = (string)Str::uuid();
        $jobs = [];

        foreach ($capeCod->supervisors()->where('status', 1)->sortBy('firstname') as $supervisor) {
            if ($supervisor->name === 'TO BE ALLOCATED') continue;

            $report = Report::create([
                'user_id' => $supervisor->id,
                'company_id' => $capeCod->id,
                'name' => "Supervisor Site Plan ({$supervisor->initials}).pdf",
                'path' => "report/{$capeCod->id}",
                'type' => 'site-plan',
                'status' => 'pending',
                'batch_id' => $batchId,
            ]);

            // Build and snapshot the canonical planner data before dispatch so
            // every PDF in this batch represents the same reporting window.
            $data = SitePlannerDataBuilder::build([
                'date' => $reportDate->format('Y-m-d'),
                'weeks' => 6,
                'mode' => 'supervisor',
                'site_ids' => [],
                'supervisor_ids' => [$supervisor->id],
            ]);

            $jobs[] = new SitePlannerPdf($report->id, $data, 'pdf.plan-site');
        }

        echo 'Supervisor site-plan PDFs queued: ' . count($jobs) . "\n";

        if (!$jobs) {
            echo "No active Supervisor reports to generate. No email required.\n";
            return 0;
        }

        // Keep the legacy group as the fallback. When the batch callback sends
        // the email, the central listener can append or replace these recipients.
        $emailList = $capeCod->notificationsUsersEmailType('site.supervisor.export');

        Bus::batch($jobs)->name('Supervisor Site Export')->finally(function (Batch $batch) use ($batchId, $emailList) {
            Log::info('Supervisor export batch finished', [
                'batch_id' => $batch->id,
                'total' => $batch->totalJobs,
                'failed' => $batch->failedJobs,
                'processed' => $batch->processedJobs(),
            ]);

            // Failed PDF jobs are deliberately omitted; the email contains all
            // successful exports and the scheduler records any child-job failure.
            $reports = Report::query()->where('batch_id', $batchId)->where('status', 'completed')->get();
            if ($reports->isEmpty()) {
                Log::warning('Supervisor export finished with no completed reports', ['batch_id' => $batch->id]);
                return;
            }

            $attachments = $reports->map(fn(Report $report) => "{$report->path}/{$report->name}")->all();
            $mailable = new SiteSupervisorSiteExport($attachments);
            if ($emailList) $mailable->to($emailList);
            $mailable->send(app('mailer'));

            Log::info('Supervisor export email sent', ['batch_id' => $batch->id, 'attached' => count($attachments)]);
        })->dispatch();

        echo "Supervisor Site Export batch dispatched.\n";

        return count($jobs);
    }
}
