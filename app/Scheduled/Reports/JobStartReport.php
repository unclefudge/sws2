<?php

namespace App\Scheduled\Reports;

use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class JobStartReport implements ScheduledOperationHandler
{
    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.jobstart',
            'name' => 'Job Start',
            'category' => 'report',
            'description' => 'Emails a PDF showing upcoming job start dates and their current compliance details.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Legacy notification group: site.jobstartexport; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    /**
     * Build the upcoming job-start report and email its PDF export.
     */
    public function handle(): int
    {
        $planner = DB::table('site_planner as p')->select(['p.site_id', 'p.entity_type', 'p.entity_id', 'p.from'])
            ->join('trade_task as t', 'p.task_id', '=', 't.id')
            ->where('p.from', '>=', Carbon::today())
            ->where('t.code', 'START')->orderBy('p.from')->get();

        // Load related records in batches rather than querying once for every planner row.
        $sites = Site::whereIn('id', $planner->pluck('site_id')->unique())->get()->keyBy('id');
        $companyNames = Company::whereIn('id', $planner->where('entity_type', 'c')->pluck('entity_id')->filter()->unique())->pluck('name', 'id');
        $startData = [];

        foreach ($planner as $plan) {
            $site = $sites->get($plan->site_id);
            if (!$site || (int)$site->status !== 1) continue;

            $entityName = $plan->entity_type === 'c' ? ($companyNames->get($plan->entity_id) ?? 'Unknown company') : 'Carpenter';
            $startData[] = [
                'date' => Carbon::parse($plan->from)->format('M j'),
                'code' => $site->code,
                'name' => $site->name,
                'company' => $entityName,
                'supervisor' => $site->supervisorName,
                'contract_sent' => $site->contract_sent?->format('d/m/Y') ?? '-',
                'contract_signed' => $site->contract_signed?->format('d/m/Y') ?? '-',
                'deposit_paid' => $site->deposit_paid?->format('d/m/Y') ?? '-',
                'eng' => $site->engineering ? 'Y' : '-',
                'cc' => $site->construction_rcvd?->format('d/m/Y') ?? '-',
                'hbcf' => $site->hbcf_start?->format('d/m/Y') ?? '-',
                'consultant' => $site->consultant_name,
            ];
        }

        echo 'Upcoming active job starts: ' . count($startData) . "\n";

        // This remains the legacy/default recipient source. The central mail
        // listener can append dashboard rules or replace this list entirely.
        $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.jobstartexport');

        $directory = storage_path('app/tmp');
        File::ensureDirectoryExists($directory);
        $file = $directory . '/jobstart-' . now()->format('Ymd-His-u') . '.pdf';

        try {
            \PDF::loadView('pdf/plan-jobstart', ['startdata' => $startData])->setPaper('A4', 'landscape')->save($file);

            $data = ['user_fullname' => 'Auto Generated', 'user_company_name' => 'Cape Cod', 'startdata' => $startData,];
            Mail::send('emails/jobstart', $data, function ($message) use ($emailList, $file) {
                $message->from('do-not-reply@safeworksite.com.au', 'Safe Worksite');
                // An empty legacy list is valid: managed/append rules may add
                // the recipients during the MessageSending event.
                if ($emailList) $message->to($emailList);
                $message->subject('Upcoming Job Start Dates');
                $message->attach($file);
            });

            echo "Job Start report sent.\n";
        } finally {
            File::delete($file);
        }

        return 1;
    }
}
