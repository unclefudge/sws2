<?php

namespace App\Scheduled\Operations;

use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\Site;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Carbon\Carbon;

class NonAttendeesOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.non_attendees',
            'name' => 'Create non-attendance records',
            'category' => 'attendance',
            'description' => 'Finds rostered workers who did not attend during the previous seven days and records them as non-compliant.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'sendsEmail' => false,
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $yesterday = Carbon::yesterday();
        $lastWeek = Carbon::today()->subDays(7);
        $roster = SiteRoster::query()->whereDate('date', '>=', $lastWeek)->whereDate('date', '<=', $yesterday)->orderBy('site_id')->get();
        $sites = Site::query()->whereIn('id', $roster->pluck('site_id')->unique())->get()->keyBy('id');
        $users = User::query()->with('company')->whereIn('id', $roster->pluck('user_id')->unique())->get()->keyBy('id');
        $createdCount = 0;

        echo 'Checking roster entries from ' . $lastWeek->format('d/m/Y') . ' to ' . $yesterday->format('d/m/Y') . ".\n";

        foreach ($roster as $rosterEntry) {
            $date = Carbon::parse($rosterEntry->date);
            if (!$date->isWeekday()) continue;

            $site = $sites->get($rosterEntry->site_id);
            $user = $users->get($rosterEntry->user_id);
            if (!$site || !$user) {
                echo "Skipped roster {$rosterEntry->id}: its Site or User no longer exists.\n";
                continue;
            }

            if ($site->isUserOnsite($user->id, $rosterEntry->date) || $site->isUserOnCompliance($user->id, $rosterEntry->date)) continue;

            // firstOrCreate makes a manual retry safe even if the first attempt
            // stopped after creating only some of the required records.
            $compliance = SiteCompliance::firstOrCreate(['site_id' => $site->id, 'user_id' => $user->id, 'date' => $rosterEntry->date,],
                ['reason' => null, 'status' => 0, 'resolved_at' => '0000-00-00 00:00:00',]);

            if (!$compliance->wasRecentlyCreated) continue;

            $companyName = $user->company?->name_alias ?: 'Unknown company';
            echo $date->format('d/m/Y') . " {$site->name} ({$site->code}) - {$user->fullname} ({$companyName}) was absent.\n";
            $createdCount++;
        }


        $resolvedCount = SiteCompliance::query()->where('status', 0)->where(function ($query) {
            $query->where('reason', 0)->orWhereNull('reason');
        })
            ->update(['reason' => 1, 'status' => 1, 'notes' => 'Nightly batch not logged in users as non-compliant', 'resolved_at' => Carbon::now()->toDateTimeString(),]);

        echo "Non-attendance records created: {$createdCount}.\n";
        echo "Open non-attendance records finalised: {$resolvedCount}.\n";

        return $createdCount + $resolvedCount;
    }
}
