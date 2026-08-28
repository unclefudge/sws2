<?php

namespace App\Scheduled\Operations;

use App\Models\Safety\ToolboxTalk;
use App\Scheduled\Contracts\ScheduledOperationHandler;

class ArchiveToolboxOperation implements ScheduledOperationHandler
{
    public static function scheduledOperation(): array
    {
        return [
            'key' => 'nightly.archive_toolbox',
            'name' => 'Archive completed toolbox talks',
            'category' => 'maintenance',
            'description' => 'Archives active toolbox talks when everyone has completed them or every outstanding user is inactive.',
            'schedule' => ['type' => 'daily', 'time' => '00:05'],
            'recipients' => 'No email is sent by this operation',
            'clientConfigurable' => false,
        ];
    }

    public function handle(): int
    {
        $talks = ToolboxTalk::query()->where('master', 0)->where('status', 1)->get();
        $archivedCount = 0;

        echo "Active toolbox talks checked: {$talks->count()}.\n";

        foreach ($talks as $talk) {
            // Resolve the outstanding users once. The legacy method called
            // outstandingBy() twice for every talk, repeating the same work.
            $outstanding = collect($talk->outstandingBy());
            $reason = null;

            if ($outstanding->isEmpty()) {
                $reason = 'all assigned users have completed the talk';
            } elseif (!$outstanding->contains(fn($user) => (int) $user->status === 1)) {
                $reason = 'all outstanding users are inactive';
            }

            if (!$reason) continue;

            $talk->status = -1;
            $talk->save();
            $archivedCount++;
            echo "Archived toolbox talk [{$talk->id}] {$talk->name}: {$reason}.\n";
        }

        echo "Toolbox talks archived: {$archivedCount}.\n";

        return $archivedCount;
    }
}
