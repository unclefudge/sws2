<?php

namespace App\Services\Planner;

use App\Models\Site\Planner\PublicHoliday;
use App\Models\Site\Planner\PlannerClosure;
use App\Models\Site\Planner\SitePlanner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PlannerCalendarChangeService
{
    public function __construct(protected PlannerDateService $dates)
    {
    }

    /** Build the exact proposed changes, without writing any records. */
    public function preview(?int $holidayId, ?array $attributes, bool $lock = false, string $source = 'public'): array
    {
        $calendarQuery = PublicHoliday::orderBy('id');
        $closureQuery = PlannerClosure::orderBy('id');
        $taskQuery = SitePlanner::with(['site', 'task'])
            ->whereDate('to', '>=', Carbon::today())->orderBy('id');
        if ($lock) {
            $calendarQuery->lockForUpdate();
            $closureQuery->lockForUpdate();
            $taskQuery->lockForUpdate();
        }
        $publicEntries = $calendarQuery->get();
        $closureEntries = $closureQuery->get();
        $entries = collect()->concat($publicEntries)->concat($closureEntries);
        $targetEntries = $source === 'closure' ? $closureEntries : $publicEntries;
        $model = $source === 'closure' ? PlannerClosure::class : PublicHoliday::class;
        $tasks = $taskQuery->get();
        if ($holidayId) {
            $entry = $targetEntries->firstWhere('id', $holidayId);
            abort_unless($entry, 404);
            // Never rewrite the historical calendar, including an ongoing closure.
            if ($entry->date->startOfDay()->lt(Carbon::today())) {
                throw ValidationException::withMessages(['date' => 'Only entries starting today or later can be changed or removed.']);
            }
        }

        $oldDates = PublicHoliday::expandDates($entries);
        $newEntries = $entries->reject(fn ($entry) => $entry->getTable() === (new $model())->getTable() && (int) $entry->id === $holidayId)->values();
        if ($attributes !== null) {
            $newEntries->push(new $model($attributes));
        }
        $newDates = PublicHoliday::expandDates($newEntries);
        $rows = [];
        $state = [];
        foreach ($tasks as $task) {
            $from = $task->from->format('Y-m-d');
            $to = $task->to->format('Y-m-d');
            $rows[] = [
                'id' => (int) $task->id, 'site_id' => (int) $task->site_id,
                'entity_type' => $task->entity_type, 'entity_id' => (int) $task->entity_id,
                'task_id' => (int) $task->task_id, 'from' => $from, 'to' => $to,
                'days' => (int) $task->days, 'weekend' => (bool) $task->weekend,
                'site' => $task->site?->name ?? "Site #{$task->site_id}",
                'task' => $task->task?->name ?? "Task #{$task->task_id}",
            ];
            $state[] = [$task->id, $task->site_id, $task->entity_type, $task->entity_id,
                $task->task_id, $from, $to, (int) $task->days, (bool) $task->weekend,
                (string) $task->updated_at];
        }
        $review = $this->reflow($rows, $oldDates, $newDates, Carbon::today()->toDateString());

        $calendarState = $entries->map(fn ($entry) => [
$entry->getTable(), $entry->id, $entry->name, $entry->type, $entry->date->format('Y-m-d'),
            $entry->end_date?->format('Y-m-d'), (int) $entry->status, (string) $entry->updated_at,
        ])->all();

        return [
            'changes' => $review['changes'],
            'blockers' => $review['blockers'],
            'fingerprint' => hash('sha256', json_encode([
                'isolated-site-reflow-v3', $source, Carbon::today()->format('Y-m-d'), $holidayId, $attributes, $calendarState, $state,
            ], JSON_THROW_ON_ERROR)),
        ];
    }

    /**
     * Rebuild each affected site's remaining weekday schedule on the proposed
     * calendar. Old working-day offsets are preserved, including intentional
     * parallel tasks and gaps. This pure calculation is also used by the tests.
     */
    public function reflow(array $rows, array $oldDates, array $newDates, string $today): array
    {
        $old = $this->dates->withHolidays($oldDates);
        $new = $this->dates->withHolidays($newDates);
        $changedDates = array_values(array_unique(array_merge(
            array_diff(array_keys($oldDates), array_keys($newDates)),
            array_diff(array_keys($newDates), array_keys($oldDates)),
        )));
        sort($changedDates);
        $anchors = [];
        $touched = [];
        $reasons = [];
        $blockers = [];
        $changes = [];
        $final = [];

        // A site's first task touching an actually changed weekday anchors the
        // reflow. Sites with no affected work retain their existing schedule.
        foreach ($rows as $row) {
            foreach ($changedDates as $date) {
                if ($date < $today || $date < $row['from'] || $date > $row['to']) {
                    continue;
                }
                if ($row['weekend'] || !$old->parse($date)->isWeekend()) {
                    $touched[$row['id']] = true;
                    break;
                }
            }
            if (!isset($touched[$row['id']])) {
                continue;
            }
            if ($this->isChristmasPlaceholder($row)) {
                $reasons[$row['id']] = 'Remove this old Christmas Holidays / Tools Down placeholder before applying the closure.';
                continue;
            }
            if (!$row['weekend']) {
                $anchor = max($today, $row['from']);
                $site = $row['site_id'];
                $anchors[$site] = isset($anchors[$site]) ? min($anchors[$site], $anchor) : $anchor;
            }
        }

        $siteCalendars = [];
        foreach ($anchors as $site => $anchor) {
            $oldAnchor = $old->parse($anchor);
            $newAnchor = $new->parse($anchor);
            while (!$old->isWorkDay($oldAnchor)) {
                $oldAnchor->addDay();
            }
            while (!$new->isWorkDay($newAnchor)) {
                $newAnchor->addDay();
            }
            $siteCalendars[$site] = [$oldAnchor, $newAnchor];
        }
        $mappedStarts = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            $site = $row['site_id'];
            $from = $row['from'];
            $to = $row['to'];
            $newFrom = $from;
            $newTo = $to;
            $reason = $reasons[$id] ?? '';
            $downstream = isset($anchors[$site]) && $from >= $anchors[$site];
            $affected = isset($touched[$id]) || $downstream;

            if ($to < $today) {
                $affected = false;
            }
            if ($affected && $reason === '' && $this->isChristmasPlaceholder($row)) {
                $reason = 'Remove this old Christmas Holidays / Tools Down placeholder before applying the closure.';
            }
            if ($affected && $reason === '' && $row['weekend']) {
                $reason = 'Review and move or split this weekend task manually before applying the site schedule change.';
            }
            if ($affected && $reason === '' && !$old->isWorkDay($from)) {
                $reason = 'This task already starts on a non-working date in the current calendar. Review its start manually before applying the change.';
            }
            if ($affected && $reason === '') {
                if ($downstream) {
                    if (!isset($mappedStarts[$site][$from])) {
                        [$oldAnchor, $newAnchor] = $siteCalendars[$site];
                        $offset = max(0, $old->workDaysBetween($oldAnchor, $from) - 1);
                        $mappedStarts[$site][$from] = $new->shift($newAnchor, $offset)->toDateString();
                    }
                    $newFrom = $mappedStarts[$site][$from];
                }
                // A task already started retains its historical start; only its
                // remaining finish changes. Quantities always remain unchanged.
                $newTo = $new->endDate($newFrom, max(1, $row['days']))->toDateString();
            }
            if ($reason !== '') {
                $blockers[] = $this->taskLabel($row) . ': ' . $reason;
            }
            $key = implode(':', [$site, $row['entity_type'], $row['entity_id'], $row['task_id'], (int) $row['weekend']]);
            $final[$key][] = array_merge($row, ['new_from' => $newFrom, 'new_to' => $newTo]);
            if ($newFrom !== $from || $newTo !== $to || $reason !== '') {
                $changes[$id] = array_merge($row, ['new_from' => $newFrom, 'new_to' => $newTo, 'reason' => $reason]);
            }
        }

        foreach ($final as $group) {
            usort($group, fn ($a, $b) => [$a['new_from'], $a['id']] <=> [$b['new_from'], $b['id']]);
            foreach ($group as $i => $left) {
                for ($j = $i + 1; $j < count($group) && $group[$j]['new_from'] <= $left['new_to']; $j++) {
                    $right = $group[$j];
                    if (isset($changes[$left['id']]) || isset($changes[$right['id']])) {
                        $from = $new->parse(max($left['new_from'], $right['new_from']))->format('d/m/Y');
                        $to = $new->parse(min($left['new_to'], $right['new_to']))->format('d/m/Y');
                        $blockers[] = "{$left['site']} — {$left['task']} (tasks #{$left['id']} and #{$right['id']}): overlap on {$from} – {$to} for the same company/trade. Move or split these entries before applying.";
                    }
                }
            }
        }
        usort($changes, fn ($a, $b) => [$a['site'], $a['new_from'], $a['id']] <=> [$b['site'], $b['new_from'], $b['id']]);

        return ['changes' => array_values($changes), 'blockers' => array_values(array_unique($blockers))];
    }

    private function taskLabel(array $row): string
    {
        return "{$row['site']} — {$row['task']} (task #{$row['id']})";
    }

    private function isChristmasPlaceholder(array $row): bool
    {
        return (int) $row['task_id'] === 403 && $row['entity_type'] === 't' && (int) $row['entity_id'] === 45;
    }

    /** Called inside the controller transaction, after its fresh locked preview. */
    public function apply(?int $holidayId, ?array $attributes, array $review, string $source = 'public'): void
    {
        if ($review['blockers']) {
            throw ValidationException::withMessages(['date' => 'Resolve the listed tasks before changing the calendar.']);
        }
        $model = $source === 'closure' ? PlannerClosure::class : PublicHoliday::class;
        if ($attributes === null) {
            $model::findOrFail($holidayId)->delete();
        } elseif ($holidayId) {
            $model::findOrFail($holidayId)->update($attributes);
        } else {
            $model::create($attributes);
        }
        $this->dates->clearCache();
        foreach ($review['changes'] as $change) {
            $task = SitePlanner::findOrFail($change['id']);
            $task->from = $change['new_from'];
            $task->to = $change['new_to'];
            $task->updated_by = Auth::id();
            // Dates and overlaps have been checked as a complete batch. Avoid
            // recalculating individual rows against a partly updated schedule.
            $task->saveQuietly();
        }
    }
}
