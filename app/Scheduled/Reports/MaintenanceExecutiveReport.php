<?php

namespace App\Scheduled\Reports;

use App\Mail\Site\SiteMaintenanceExecutive;
use App\Models\Company\Company;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteMaintenanceCategory;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class MaintenanceExecutiveReport implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.maintenance_executive',
            'name' => 'Maintenance executive summary',
            'category' => 'report',
            'description' => 'Emails the quarterly executive maintenance PDF covering activity, response averages, categories and Supervisor workloads over the previous 90 days.',
            'schedule' => ['type' => 'quarterly', 'day' => 1, 'months' => [3, 6, 9, 12], 'time' => '00:05'],
            'recipients' => 'Legacy notification group: site.maintenance.executive; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $to = Carbon::now();
        $from = $to->copy()->subDays(90);
        $includedFrom = Carbon::createFromFormat('Y-m-d', '2021-05-01')->startOfDay();
        $mains = SiteMaintenance::query()->whereDate('updated_at', '>=', $from)->whereDate('updated_at', '<=', $to)->where('status', '<>', 2)->get();
        $mainsOld = SiteMaintenance::query()->whereDate('updated_at', '<', $from)->whereIn('status', [1, 3])->get();
        $mainsCreated = SiteMaintenance::query()->whereDate('created_at', '>=', $from)->whereDate('updated_at', '<=', $to)->get();
        $allMaintenance = $mains->concat($mainsOld);
        $categories = SiteMaintenanceCategory::query()->whereIn('id', $allMaintenance->pluck('category_id')->filter()->unique())->get()->keyBy('id');
        $supervisors = User::query()->whereIn('id', $allMaintenance->pluck('super_id')->filter()->unique())->get()->keyBy('id');
        $count = $excluded = 0;
        $totalAllocated = $totalCompleted = $totalContacted = $totalAppointment = 0;
        $categoryCounts = [];
        $supervisorCounts = [];

        foreach ($allMaintenance as $maintenance) {
            if ($maintenance->created_at->gte($includedFrom)) {
                $completedAt = (int)$maintenance->status === 1 ? $to : $maintenance->updated_at;
                $totalCompleted += $maintenance->reported->diffInWeekDays($completedAt);

                // Use midnight without reconstructing a malformed date string.
                // This retains the original intention of avoiding an extra partial day.
                $allocatedDays = 0;
                if ($maintenance->assigned_super_at) $allocatedDays = $maintenance->assigned_super_at->copy()->startOfDay()->diffInWeekDays($maintenance->reported);
                elseif (in_array((int)$maintenance->status, [0, 3], true)) $allocatedDays = $maintenance->reported->diffInWeekDays($maintenance->updated_at);
                elseif ((int)$maintenance->status === 1) $allocatedDays = $maintenance->reported->diffInWeekDays($to);
                $totalAllocated += $allocatedDays;

                if ($maintenance->client_contacted) $totalContacted += $maintenance->client_contacted->diffInWeekDays($maintenance->reported);
                elseif (in_array((int)$maintenance->status, [0, 3], true)) $totalContacted += $maintenance->reported->diffInWeekDays($maintenance->updated_at);
                elseif ((int)$maintenance->status === 1) $totalContacted += $maintenance->reported->diffInWeekDays($to);

                $appointmentFrom = $maintenance->client_appointment ?: $maintenance->reported;
                if (in_array((int)$maintenance->status, [0, 3], true)) $totalAppointment += $appointmentFrom->diffInWeekDays($maintenance->updated_at);
                elseif ((int)$maintenance->status === 1) $totalAppointment += $appointmentFrom->diffInWeekDays($to);
                $count++;
            } else {
                $excluded++;
            }

            $categoryName = $maintenance->category_id ? ($categories->get($maintenance->category_id)?->name ?: 'N/A') : 'N/A';
            $categoryCounts[$categoryName] = ($categoryCounts[$categoryName] ?? 0) + 1;

            $supervisorName = $maintenance->super_id ? ($supervisors->get($maintenance->super_id)?->name ?: 'N/A') : 'N/A';
            if (!isset($supervisorCounts[$supervisorName])) $supervisorCounts[$supervisorName] = [0, 0, 0];
            if ((int)$maintenance->status === 1) $supervisorCounts[$supervisorName][0]++;
            elseif ((int)$maintenance->status === 0) $supervisorCounts[$supervisorName][1]++;
            elseif ((int)$maintenance->status === 3) $supervisorCounts[$supervisorName][2]++;
        }

        ksort($categoryCounts);
        ksort($supervisorCounts);
        $avgCompleted = $count ? round($totalCompleted / $count) : 0;
        $avgAllocated = $count ? round($totalAllocated / $count) : 0;
        $avgContacted = $count ? round($totalContacted / $count) : 0;
        $avgAppointment = $count ? round($totalAppointment / $count) : 0;
        $directory = storage_path('app/tmp');
        File::ensureDirectoryExists($directory);
        $file = $directory . '/maintenance-executive-' . $to->format('Ymd-His-u') . '.pdf';

        $recentCount = $mains->count();
        $olderCount = $mainsOld->count();
        $createdCount = $mainsCreated->count();
        echo "Maintenance Executive: {$recentCount} recent, {$olderCount} older active/on-hold, {$createdCount} created.\n";

        try {
            \PDF::loadView('pdf/site/maintenance-executive', [
                'mains' => $mains,
                'mains_old' => $mainsOld,
                'mains_created' => $mainsCreated,
                'to' => $to,
                'from' => $from,
                'avg_completed' => $avgCompleted,
                'avg_allocated' => $avgAllocated,
                'avg_contacted' => $avgContacted,
                'avg_appoint' => $avgAppointment,
                'cats' => $categoryCounts,
                'supers' => $supervisorCounts,
                'excluded' => $excluded,
            ])->setPaper('A4', 'landscape')->save($file);

            $emailList = Company::findOrFail(3)->notificationsUsersEmailType('site.maintenance.executive');
            $mailable = new SiteMaintenanceExecutive($file);
            if ($emailList) $mailable->to($emailList);
            $mailable->send(app('mailer'));

            echo "Maintenance Executive report sent.\n";
        } finally {
            File::delete($file);
        }

        return 1;
    }
}
