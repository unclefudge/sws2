<?php

namespace App\Scheduled;

use App\Models\Scheduled\ScheduledRunGroup;

class ScheduledRunSummary
{
    public function refresh(?ScheduledRunGroup $group): void
    {
        if (!$group) {
            return;
        }

        $counts = $group->runs()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $finished = collect(['successful', 'failed', 'skipped', 'shadow'])->sum(fn($status) => (int) ($counts[$status] ?? 0));
        $failureCount = (int) ($counts['failed'] ?? 0);
        $successCount = (int) ($counts['successful'] ?? 0);
        $skipCount = (int) ($counts['skipped'] ?? 0) + (int) ($counts['shadow'] ?? 0);

        $status = $finished < $group->expected_count
            ? 'running'
            : ($failureCount === 0 ? 'successful' : (($successCount + $skipCount) > 0 ? 'partial' : 'failed'));

        $group->update([
            'status' => $status,
            'started_at' => $group->started_at ?: now(),
            'completed_at' => $finished >= $group->expected_count ? now() : null,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'skip_count' => $skipCount,
        ]);
    }
}
