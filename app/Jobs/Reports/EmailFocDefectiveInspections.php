<?php

namespace App\Jobs\Reports;

use App\Mail\Site\SiteFocDefectiveReport;
use App\Models\Company\Company;
use App\Models\Misc\Category;
use App\Models\Site\SiteFocItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailFocDefectiveInspections implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): int
    {
        $defectiveCategory = Category::where('type', 'foc_item')->where('name', 'Defective')->where('status', 1)->first();

        if (!$defectiveCategory) {
            Log::warning('FOC defective report skipped: Defective category not found');
            return 0;
        }

        $items = SiteFocItem::with(['foc.site', 'foc.supervisor'])->where('category_id', $defectiveCategory->id)->where('status', 1)
            ->whereHas('foc', fn($query) => $query->where('status', '<>', -1))
            ->orderBy('order')
            ->get()
            ->filter(fn($item) => $item->foc && $item->foc->super_id && $item->foc->site);

        if ($items->isEmpty()) {
            Log::info('FOC defective report: no outstanding defective items');
            return 0;
        }

        $cc = Company::findOrFail(3);

        // Supervisor is always the primary recipient. These configured users
        // are copied on each Supervisor report.
        $managementEmails = $cc->notificationsUsersEmailType('site.foc.defective');

        if (app()->environment('prod') && !$managementEmails) {
            Log::warning('FOC defective report: no CC recipients configured for site.foc.defective');
        }

        $sent = 0;

        foreach ($items->groupBy(fn($item) => (int)$item->foc->super_id) as $supervisorId => $supervisorItems) {
            $supervisor = $supervisorItems->first()->foc->supervisor;

            if (!$supervisor) {
                Log::warning('FOC defective report skipped Supervisor group: user not found', ['supervisor_id' => $supervisorId,]);
                continue;
            }

            $supervisorEmail = filter_var($supervisor->email, FILTER_VALIDATE_EMAIL) ? $supervisor->email : null;

            $jobs = $supervisorItems->groupBy(fn($item) => $item->foc_id)
                ->map(function ($focItems) {
                    $first = $focItems->first();
                    $site = $first->foc->site;

                    return [
                        'foc_id' => $first->foc_id,
                        'site_code' => $site->code,
                        'site_name' => $site->name,
                        'defects' => $focItems->map(fn($item) => ['name' => $item->name, 'updated_at' => $item->updated_at?->format('d/m/Y') ?? '-',]
                        )->values()->all(),
                    ];
                })
                ->sortBy('site_code')
                ->values()
                ->all();

            if (app()->environment('prod')) {
                // If the Supervisor has no valid email, send it to the management
                // recipients instead so the report is not silently lost.
                $emailTo = $supervisorEmail ? [$supervisorEmail] : $managementEmails;
                $emailCc = $supervisorEmail ? array_values(array_diff($managementEmails, [$supervisorEmail])) : [];
            } else {
                $emailTo = [config('mail.email_dev')];
                $emailCc = [];
            }

            if (!$emailTo) {
                Log::warning('FOC defective report skipped: no valid recipients', ['supervisor_id' => $supervisor->id, 'supervisor' => $supervisor->name,]);
                continue;
            }

            try {
                $mail = Mail::to($emailTo);

                if ($emailCc) {
                    $mail->cc($emailCc);
                }

                $mail->send(new SiteFocDefectiveReport(supervisorName: $supervisor->name, supervisorFirstName: $supervisor->firstname ?: $supervisor->name, jobs: $jobs,));

                Log::info('FOC defective report sent', ['supervisor_id' => $supervisor->id, 'to' => $emailTo, 'cc' => $emailCc, 'jobs' => count($jobs), 'defects' => $supervisorItems->count(),]);
                $sent++;
            } catch (\Throwable $e) {
                // One Supervisor email must not prevent the remaining Supervisors
                // from receiving their reports. When this method is running inside
                // the new scheduler, also close the MessageSending audit row. The
                // outer runner will flag the operation after every Supervisor has
                // been attempted, without retrying already-sent emails.
                if ($runId = app(\App\Scheduled\ScheduledRunContext::class)->runId()) {
                    $messageAudit = \App\Models\Scheduled\ScheduledReportMessage::where('scheduled_run_id', $runId)
                        ->where('status', 'sending')
                        ->latest('id')
                        ->first();

                    $messageAudit?->update([
                            'status' => 'failed',
                            'failed_at' => now(),
                            'error' => $e->getMessage(),
                    ]);
                }

                Log::error('FOC defective Supervisor report failed', ['supervisor_id' => $supervisor->id, 'supervisor' => $supervisor->name, 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(),]);
            }
        }

        return $sent;
    }
}
