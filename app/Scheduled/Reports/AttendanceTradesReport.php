<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteTradesAttendance;
use App\Models\Company\Company;
use App\Models\Site\Planner\SiteAttendance;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class AttendanceTradesReport implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.trades_attendance',
            'name' => 'Trades attendance',
            'category' => 'report',
            'description' => 'Emails monthly attendance PDFs for active onsite companies and lists companies with no attendance.',
            'schedule' => ['type' => 'monthly_day', 'day' => 1, 'time' => '00:05'],
            'recipients' => 'Legacy notification group: site.attendance.trades; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $from = new Carbon('first day of last month');
        $to = new Carbon('last day of last month');
        $companies = Company::query()->with('staff')->where('status', 1)->where('parent_company', 3)->whereNot('name', 'like', 'Cc-%')
            ->whereIn('category', [1, 2])->orderBy('name')->get();
        $staffIds = $companies->flatMap(fn(Company $company) => $company->staff->pluck('id'))->unique();
        $allAttendance = SiteAttendance::query()->with(['user', 'site'])->whereIn('user_id', $staffIds)->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)->orderBy('date')->get();
        $directory = storage_path('app/tmp/trades-attendance-' . Carbon::now()->format('Ymd-His-u'));
        File::ensureDirectoryExists($directory);
        $attendanceFiles = [];
        $nonAttendance = [];

        try {
            foreach ($companies as $company) {
                $companyStaffIds = $company->staff->pluck('id');
                $attendance = $allAttendance->whereIn('user_id', $companyStaffIds)->sortBy('date');
                $attendanceCount = $attendance->count();
                echo "{$company->name}: {$attendanceCount} attendance record(s).\n";

                if ($attendance->isEmpty()) {
                    $nonAttendance[] = $company->name;
                    continue;
                }

                $data = [];
                foreach ($attendance as $record) {
                    $date = $record->date->format('D M d, Y');
                    $data[$date][$record->site->name][$record->user->id] = $record->user->full_name;
                }

                $file = $directory . '/' . sanitizeFilename($company->name) . ' Monthly Attendance.pdf';
                \PDF::loadView('pdf/company-attendance', compact('data', 'company', 'from', 'to'))->setPaper('a4', 'landscape')->save($file);
                $attendanceFiles[] = $file;
            }

            $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.attendance.trades');
            $mailable = new SiteTradesAttendance($attendanceFiles, $nonAttendance);
            if ($emailList) $mailable->to($emailList);
            $mailable->send(app('mailer'));

            echo 'Trades Attendance report sent with ' . count($attendanceFiles) . " attachment(s).\n";
        } finally {
            // The mailable is sent synchronously so all generated attachments
            // can be removed immediately after the mail transport has used them.
            File::deleteDirectory($directory);
        }

        return 1;
    }
}
