<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteMaintenanceSupervisorNoActionSubReport;
use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MaintenanceSupervisorNoActionReport implements ScheduledOperationHandler
{
    public function __construct(private ScheduledDynamicRecipientResolver $recipientResolver, private ScheduledReportMailer $mailer)
    {
    }

    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.maintenance_supervisor_no_action',
            'name' => 'Maintenance supervisor no action',
            'category' => 'report',
            'description' => 'Emails each relevant Site Supervisor their active maintenance requests without an appointment or action for 14 days.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [2], 'time' => '00:05'], // Tuesday
            'recipients' => 'Relevant Site Supervisor (To) and legacy site.maintenance.super.noaction group (CC); dashboard recipients can append to or replace the legacy group',
            'dynamicRecipients' => [
                ['key' => 'site_supervisor', 'label' => 'Site Supervisor', 'delivery' => 'to', 'description' => 'The Supervisor assigned to the maintenance requests in the individual email.', 'required' => true],
            ],
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $maintenanceRequests = SiteMaintenance::query()->with('site')->where('status', 1)->orderBy('reported')->get();
        $supervisors = User::query()->whereIn('id', $maintenanceRequests->pluck('super_id')->filter()->unique())->get()->keyBy('id');
        $managementEmails = Company::findOrFail(3)->notificationsUsersEmailType('site.maintenance.super.noaction');
        $now = Carbon::now();
        $recentTaskCutoff = $now->copy()->subDays(7);
        $nextTaskCutoff = $now->copy()->addDays(7);
        $inactiveCutoff = $now->copy()->subDays(14);
        $groups = $maintenanceRequests->groupBy(fn($request) => (int)($request->super_id ?: 0))->map(function ($requests, $supervisorId) use ($supervisors) {
            $supervisor = $supervisorId ? $supervisors->get($supervisorId) : null;
            return ['supervisor' => $supervisor, 'name' => $supervisor?->fullname ?: 'Unassigned', 'requests' => $requests];
        })->sortBy('name');
        $emailsSent = 0;

        foreach ($groups as $group) {
            $noAppointments = $group['requests']->filter(fn($request) => !$request->client_appointment);
            $noAction = $group['requests']->filter(function ($request) use ($recentTaskCutoff, $nextTaskCutoff, $inactiveCutoff) {
                $site = $request->site;
                if (!$site) return false;

                // Match the legacy exclusion rules: an inactive request is omitted
                // when its site has recent, imminent or any future planned work.
                $recentTask = $site->jobRecentTask;
                $nextTask = $site->jobNextTask;
                $hasRecentTask = $recentTask && $recentTask->gt($recentTaskCutoff);
                $hasNextTask = $nextTask && $nextTask->lt($nextTaskCutoff);
                // futureTasks() returns an Eloquent collection rather than a
                // relationship query, so collection methods must be used here.
                $hasFutureTasks = $site->futureTasks()->isNotEmpty();

                return $request->lastUpdated()->lt($inactiveCutoff) && !$hasRecentTask && !$hasNextTask && !$hasFutureTasks;
            });

            $noAppointmentCount = $noAppointments->count();
            $noActionCount = $noAction->count();
            echo "{$group['name']} - no appointments: {$noAppointmentCount}, no action for 14 days: {$noActionCount}\n";
            if ($noAppointments->isEmpty() && $noAction->isEmpty()) continue;

            $body = $group['name'] . '<br>' . $this->maintenanceTable('No Appointment', $noAppointments) . '<br><br>' . $this->maintenanceTable('No Actions in last 14 days', $noAction);
            $mailable = new SiteMaintenanceSupervisorNoActionSubReport($body);
            if ($managementEmails) {
                // The legacy method used management as the primary recipient
                // when no valid Supervisor was assigned, otherwise as CC.
                if ($group['supervisor']) $mailable->cc($managementEmails);
                else $mailable->to($managementEmails);
            }
            $dynamicRecipients = $this->recipientResolver->user('site_supervisor', 'Relevant Site Supervisor', $group['supervisor'], 'to');
            $this->mailer->send($mailable, $dynamicRecipients);
            $emailsSent++;
        }

        if (!$emailsSent) echo "No email required.\n";

        return $emailsSent;
    }

    private function maintenanceTable(string $heading, Collection $requests): string
    {
        $html = "<h3>{$heading}</h3><br><table style='border: 1px solid; border-collapse: collapse'><thead>";
        $html .= "<tr style='background-color: #F6F6F6; font-weight: bold; border: 1px solid; padding: 3px'>";
        $html .= "<th width='50' style='border: 1px solid'>#</th><th width='80' style='border: 1px solid'>Reported</th>";
        $html .= "<th width='200' style='border: 1px solid'>Site</th><th width='80' style='border: 1px solid'>Client Contacted</th>";
        $html .= "<th width='80' style='border: 1px solid'>Appointment</th><th width='80' style='border: 1px solid'>Last Action</th>";
        $html .= "<th width='400' style='border: 1px solid'>Note</th></tr></thead><tbody>";

        if ($requests->isEmpty()) $html .= "<tr><td colspan='7'>No Maintenance Requests found matching required criteria</td></tr>";
        foreach ($requests as $request) $html .= $this->maintenanceRow($request);

        return $html . '</tbody></table>';
    }

    private function maintenanceRow(SiteMaintenance $request): string
    {
        $clientContacted = $request->client_contacted ? $request->client_contacted->format('d/m/Y') : '-';
        $clientAppointment = $request->client_appointment ? $request->client_appointment->format('d/m/Y') : '-';
        $lastAction = $request->lastAction();
        $lastActionDate = $lastAction ? $lastAction->updated_at->format('d/m/Y') : $request->created_at->format('d/m/Y');
        $siteName = $request->site?->name ?: 'Unknown site';
        $reportedDate = $request->created_at->format('d/m/Y');
        $lastActionNote = $request->lastActionNote();

        return "<tr><td style='border: 1px solid'>M{$request->code}</td><td style='border: 1px solid'>{$reportedDate}</td>"
            . "<td style='border: 1px solid'>{$siteName}</td><td style='border: 1px solid'>{$clientContacted}</td>"
            . "<td style='border: 1px solid'>{$clientAppointment}</td><td style='border: 1px solid'>{$lastActionDate}</td>"
            . "<td style='border: 1px solid'>{$lastActionNote}</td></tr>";
    }
}
