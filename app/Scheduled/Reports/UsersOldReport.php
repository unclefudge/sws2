<?php

namespace App\Scheduled\Reports;

use App\Mail\User\OldUsers;
use App\Models\Company\Company;
use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Carbon\Carbon;

class UsersOldReport implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'report.old_users',
            'name' => 'Old users',
            'category' => 'report',
            'description' => 'Emails active onsite trade users who have never logged in or have not logged in for three months despite their company remaining active on the planner.',
            'schedule' => ['type' => 'monthly_nth_weekday', 'weekday' => 2, 'occurrence' => 1, 'time' => '00:05'], // First Tuesday
            'recipients' => 'Legacy notification group: user.oldusers; dashboard recipients can append to or replace this group',
            'clientConfigurable' => true,
        ];
    }

    public function handle(): int
    {
        $capeCod = Company::findOrFail(3);
        $threeMonthsAgo = Carbon::today()->subMonths(3);
        $capeCodUserIds = $capeCod->users(1)->pluck('id');
        $candidates = User::query()->with('company')->where('status', 1)->where('onsite', 1)->whereIn('id', $capeCodUserIds)
            ->where(function ($query) use ($threeMonthsAgo) {
                $query->whereNull('last_login')->orWhereDate('last_login', '<', $threeMonthsAgo);
            })->orderBy('company_id')->get();
        $lastPlannerDates = [];

        $users = $candidates->filter(function (User $user) use ($threeMonthsAgo, &$lastPlannerDates) {
            $company = $user->company;
            if (!$company || !in_array((int)$company->category, [1, 2], true) || (int)$company->status !== 1 || !$user->hasAnyRole2('ext-leading-hand|tradie|labourers')) return false;
            if (!$user->last_login) return true;

            if (!array_key_exists($company->id, $lastPlannerDates)) $lastPlannerDates[$company->id] = $company->lastDateOnPlanner();
            $lastPlannerDate = $lastPlannerDates[$company->id];

            return $lastPlannerDate && $user->last_login->lt($threeMonthsAgo) && $user->last_login->lt($lastPlannerDate);
        })->values();

        echo 'Old users: ' . $users->count() . "\n";

        if ($users->isEmpty()) {
            echo "No email required.\n";
            return 0;
        }

        $emailList = $capeCod->notificationsUsersEmailType('user.oldusers');
        $mailable = new OldUsers($users);
        if ($emailList) $mailable->to($emailList);
        $mailable->send(app('mailer'));

        echo "Old Users report sent.\n";

        return 1;
    }
}
