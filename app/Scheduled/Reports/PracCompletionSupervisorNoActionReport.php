<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SitePracCompletionSupervisorNoActionReport as PracCompletionMailable;
use App\Models\Site\SitePracCompletion;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\User;
use Carbon\Carbon;

class PracCompletionSupervisorNoActionReport implements ScheduledOperationHandler
{
    public function __construct(private ScheduledDynamicRecipientResolver $recipientResolver, private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.prac_completion_supervisor_no_action',
            'name' => 'Practical completion supervisor no action',
            'category' => 'report',
            'description' => 'Emails each relevant Site Supervisor their practical completions with no update or new note for 14 days.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [2], 'time' => '00:05'], // Tuesday
            'recipients' => 'Relevant Site Supervisor (To) and legacy management contacts (CC); dashboard recipients can append to or replace the legacy contacts',
            'dynamicRecipients' => [
                ['key' => 'site_supervisor', 'label' => 'Site Supervisor', 'delivery' => 'to', 'description' => 'The Supervisor assigned to the practical completions in the individual email.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $practicalCompletions = SitePracCompletion::query()->with('site')->where('status', 1)->orderBy('created_at')->get();
        $supervisors = User::query()->whereIn('id', $practicalCompletions->pluck('super_id')->filter()->unique())->get()->keyBy('id');
        $inactiveCutoff = Carbon::now()->subDays(14);
        $groups = $practicalCompletions->groupBy(fn($completion) => (int)($completion->super_id ?: 0))->map(function ($completions, $supervisorId) use ($supervisors) {
            $supervisor = $supervisorId ? $supervisors->get($supervisorId) : null;
            return ['supervisor' => $supervisor, 'name' => $supervisor?->fullname ?: 'Unassigned', 'completions' => $completions];
        })->sortBy('name');
        $legacyManagement = ['kirstie@capecod.com.au', 'ross@capecod.com.au', 'damian@capecod.com.au'];
        $emailsSent = 0;

        foreach ($groups as $group) {
            // Cache lastUpdated() because it can inspect notes/actions and the
            // legacy method called it twice for every included completion.
            $stale = $group['completions']->map(fn($completion) => ['completion' => $completion, 'updated' => $completion->lastUpdated()])
                ->filter(fn($item) => $item['updated']->lt($inactiveCutoff));
            $staleCount = $stale->count();
            echo "{$group['name']} - no action for 14 days: {$staleCount}\n";
            if ($stale->isEmpty()) continue;

            $body = "{$group['name']}<br><br><table style='width:100%; border: 1px solid; border-collapse: collapse'><thead>";
            $body .= "<tr style='background-color: #F6F6F6; font-weight: bold; border: 1px solid; padding: 3px'>";
            $body .= "<th style='width:80px; border: 1px solid'>Created</th><th style='width:250px; border: 1px solid'>Site</th>";
            $body .= "<th style='width:500px; border: 1px solid'>Assigned Company</th><th style='width:80px; border: 1px solid'>Updated</th></tr></thead><tbody>";

            foreach ($stale as $item) {
                $completion = $item['completion'];
                $siteName = $completion->site?->name ?: 'Unknown site';
                $createdDate = $completion->created_at->format('d/m/Y');
                $assignedCompanies = $completion->assignedToNames();
                $updatedDate = $item['updated']->format('d/m/Y');
                $body .= "<tr><td style='border: 1px solid'>{$createdDate}</td><td style='border: 1px solid'>{$siteName}</td>";
                $body .= "<td style='border: 1px solid'>{$assignedCompanies}</td><td style='border: 1px solid'>{$updatedDate}</td></tr>";
            }

            $mailable = new PracCompletionMailable($body . '</tbody></table>');
            // Match the old fallback: management becomes To when no Supervisor
            // is assigned, otherwise it remains CC.
            if ($group['supervisor']) $mailable->cc($legacyManagement);
            else $mailable->to($legacyManagement);
            $dynamicRecipients = $this->recipientResolver->user('site_supervisor', 'Relevant Site Supervisor', $group['supervisor'], 'to');
            $this->mailer->send($mailable, $dynamicRecipients);
            $emailsSent++;
        }

        if (!$emailsSent) echo "No email required.\n";

        return $emailsSent;
    }
}
