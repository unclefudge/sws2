<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteSupervisorAttendanceReport;
use App\Models\Company\Company;
use App\Models\Site\Planner\SiteAttendance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\Scheduled\ScheduledDynamicRecipientResolver;
use App\Scheduled\ScheduledReportMailer;
use Carbon\Carbon;

class AttendanceSupervisorReport implements ScheduledOperationHandler
{
    public function __construct(private ScheduledDynamicRecipientResolver $recipientResolver, private ScheduledReportMailer $mailer)
    {
    }

    /**
     * These defaults are used when the report is first added. After that, its
     * schedule, recipients and enabled state are managed from the dashboard.
     */
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.supervisor_attendance',
            'name' => 'Supervisor attendance',
            'category' => 'report',
            'description' => 'Emails each Supervisor their attendance for the previous seven days.',
            'schedule' => ['type' => 'weekly', 'weekdays' => [1], 'time' => '00:05',], // Monday
            'recipients' => 'Each relevant Supervisor (To) and notification group site.attendance.super (CC)',
            'dynamicRecipients' => [[
                'key' => 'supervisor',
                'label' => 'Site Supervisor',
                'delivery' => 'to',
                'description' => 'The Supervisor whose attendance is contained in the individual email.',
                'required' => true,
            ]],
            'clientConfigurable' => true,
        ];
    }

    /**
     * Email each Supervisor their attendance for the previous seven days.
     */
    public function handle(): int
    {
        $capeCod = Company::findOrFail(3);
        $supervisors = collect($capeCod->supervisors())->reject(fn($user) => (int)$user->id === 136)->values();
        $today = Carbon::today();
        $dateFrom = $today->copy()->subDays(7);
        $dateTo = $today->copy()->subDay();

        echo "Supervisors receiving attendance reports: {$supervisors->count()}\n";

        if ($supervisors->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $attendanceBySupervisor = SiteAttendance::query()->whereIn('user_id', $supervisors->pluck('id'))->whereDate('date', '>=', $dateFrom)->whereDate('date', '<=', $dateTo)->get()->groupBy('user_id');

        // This remains the legacy/default management CC source. In Managed
        // mode the central listener replaces it with the dashboard rules.
        $managementEmails = $capeCod->notificationsUsersEmailType('site.attendance.super');
        $emailsSent = 0;

        foreach ($supervisors as $supervisor) {
            $attendance = $attendanceBySupervisor->get($supervisor->id, collect());
            $mailable = new SiteSupervisorAttendanceReport($attendance, [$supervisor->id => $supervisor->name]);
            if ($managementEmails) $mailable->cc($managementEmails);

            $dynamicRecipients = $this->recipientResolver->user('supervisor', 'Relevant Supervisor', $supervisor, 'to');
            $this->mailer->send($mailable, $dynamicRecipients);

            echo "{$supervisor->name}: {$attendance->count()} attendance record(s).\n";
            $emailsSent++;
        }

        return $emailsSent;
    }
}
