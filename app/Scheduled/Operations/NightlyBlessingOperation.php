<?php

namespace App\Scheduled\Operations;

use App\Scheduled\Contracts\ScheduledOperationHandler;
use App\User;
use Carbon\Carbon;

class NightlyBlessingOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.blessing',
            'name' => 'Prayer of blessing',
            'category' => 'maintenance',
            'description' => 'Records the nightly prayer of blessing and the alphabetic worker list in the scheduled run output.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'sendsEmail' => false,
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $users = User::query()->with('company')->orderBy('firstname')->get();

        echo "+----------------------+\n";
        echo "|  Prayer of Blessing  |\n";
        echo "+----------------------+\n";
        echo ' ' . Carbon::now()->format('d/m/Y g:i a') . "\n\n";
        echo "May each of the following workers be blessed, may they be protected from injuries,\n";
        echo "may they experience a clarity of heart and mind while they work and their spirits be at peace.\n";
        echo "Today is a new day, and may they experience a freshness and freedom from past troubles and hurts,\n";
        echo "a restoration + healing of their minds, bodies and souls, plus a deeper understanding of Father God's love for them.\n\n";

        foreach ($users as $user) {
            $companyName = $user->company?->name ?? 'No company assigned';
            echo "{$user->name} ({$companyName})\n";
        }

        echo "\nAmen.\n";
        echo "Workers included: {$users->count()}.\n";

        return $users->count();
    }
}
