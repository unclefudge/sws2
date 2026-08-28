<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteQaOutstanding;
use App\Models\Company\Company;
use App\Models\Site\SiteQa;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class QaOutstandingOnHoldReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.outstanding_on_hold_qa',
            'name' => 'Outstanding and on-hold QA',
            'category' => 'report',
            'description' => 'Emails outstanding and on-hold QA reports grouped into PDF attachments by Site Supervisor.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Notification group: site.qa.outstanding',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Generate Supervisor QA PDFs and send them together when required.
     */
    public function handle(): int
    {
        $today = Carbon::now();
        $weekAgo = $today->copy()->subWeek();
        $outstandingQas = SiteQa::query()->with('site')->whereDate('updated_at', '<=', $weekAgo)->where('status', 1)->where('master', 0)->orderBy('updated_at')->get();
        $onHoldQas = SiteQa::query()->with('site')->where('status', 4)->where('master', 0)->orderBy('updated_at')->get();

        echo "Outstanding QAs: {$outstandingQas->count()}\n";
        echo "On-hold QAs: {$onHoldQas->count()}\n";

        if ($outstandingQas->isEmpty() && $onHoldQas->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $outstandingSupervisors = $this->supervisorCounts($outstandingQas);
        $onHoldSupervisors = $this->supervisorCounts($onHoldQas);
        $directory = storage_path('app/tmp');
        $runStamp = $today->format('Ymd-His-u');
        $files = [];
        File::ensureDirectoryExists($directory);

        try {
            $this->createSupervisorPdfs('Outstanding', 'qa-outstanding', $outstandingQas, $outstandingSupervisors, $today, $directory, $runStamp, $files);
            $this->createSupervisorPdfs('On Hold', 'qa-onhold', $onHoldQas, $onHoldSupervisors, $today, $directory, $runStamp, $files);

            // Use the report's existing notification group. If Append or Managed is
            // selected in the dashboard, those recipient changes are applied automatically.
            $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.qa.outstanding');
            $mailable = new SiteQaOutstanding($files, $outstandingQas, $outstandingSupervisors, $onHoldQas, $onHoldSupervisors);
            if ($emailList) $mailable->to($emailList);
            $mailable->send(app('mailer'));

            echo 'Outstanding and On-Hold QA report sent with ' . count($files) . " PDF attachment(s).\n";
        } finally {
            File::delete($files);
        }

        return 1;
    }

    private function supervisorCounts(Collection $qas): array
    {
        return $qas->groupBy(fn(SiteQa $qa) => $qa->site?->supervisorName ?: 'No Allocated Supervisor')
            ->map(fn(Collection $supervisorQas) => $supervisorQas->count())->sortKeys()->all();
    }

    private function createSupervisorPdfs(string $reportType, string $prefix, Collection $qas, array $supervisors, Carbon $today, string $directory, string $runStamp, array &$files): void
    {
        foreach ($supervisors as $supervisor => $count) {
            $slug = Str::slug($supervisor) ?: 'unallocated';
            $file = "{$directory}/{$prefix}-{$slug}-" . substr(sha1($supervisor), 0, 8) . "-{$runStamp}.pdf";
            $files[] = $file;

            \PDF::loadView('pdf/site/site-qa-outstanding', [
                'report_type' => $reportType,
                'qas' => $qas,
                'supers' => $supervisors,
                'supervisor' => $supervisor,
                'today' => $today,
            ])->setPaper('A4', 'landscape')->save($file);
        }
    }
}
