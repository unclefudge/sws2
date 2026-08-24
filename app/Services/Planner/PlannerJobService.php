<?php

namespace App\Services\Planner;

use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PlannerJobService
{
    public function __construct(
        protected PlannerDateService $dates,
        protected PlannerTaskService $tasks,
    ) {
    }

    public function addJobStart(int $siteId, string $date): int
    {
        $site = Site::findOrFail($siteId);
        $start = $this->validTargetDate($date);

        if (SitePlanner::where('site_id', $siteId)->where('task_id', 11)->exists()) {
            throw new DomainException('This site already has a Job Start. Use Move Job Start instead.');
        }

        $presets = $this->jobStartPresets();
        $missing = collect($presets)->pluck('task_id')->unique()->diff(Task::whereIn('id', collect($presets)->pluck('task_id')->unique())->pluck('id'));

        if ($missing->isNotEmpty()) {
            throw new DomainException('The Job Start preset is incomplete. Missing task IDs: ' . $missing->implode(', ') . '.');
        }

        $created = DB::transaction(function () use ($siteId, $start, $presets) {
            $created = 0;

            foreach ($presets as $preset) {
                $from = $this->presetDate($start, (int)$preset['offset']);
                $days = (int)$preset['days'];

                SitePlanner::create([
                    'site_id' => $siteId,
                    'entity_type' => $preset['entity_type'],
                    'entity_id' => $preset['entity_id'],
                    'task_id' => $preset['task_id'],
                    'from' => $from->format('Y-m-d'),
                    'to' => $this->dates->endDate($from, $days)->format('Y-m-d'),
                    'days' => $days,
                ]);

                $created++;
            }

            return $created;
        });

        $this->sendJobStartEmail($site, $start);

        return $created;
    }

    public function moveJobStart(int $siteId, string $date, string $scope = 'linked'): int
    {
        $site = Site::findOrFail($siteId);
        $target = $this->validTargetDate($date);
        $jobStart = SitePlanner::where('site_id', $siteId)->where('task_id', 11)->orderBy('from')->first();

        if (!$jobStart) {
            throw new DomainException('This site does not have a Job Start to move.');
        }

        $oldStart = $jobStart->from->copy()->startOfDay();

        if ($oldStart->isSameDay($target)) {
            throw new DomainException('Choose a different date for the Job Start.');
        }

        $scope = in_array($scope, ['linked', 'only'], true) ? $scope : 'linked';
        $moved = DB::transaction(function () use ($siteId, $jobStart, $oldStart, $target, $scope) {
            if ($scope === 'only') {
                $jobStart->from = $target->format('Y-m-d');
                $jobStart->to = $target->format('Y-m-d');
                $jobStart->days = 1;
                $jobStart->save();

                return 1;
            }

            $offset = $this->signedWorkdayOffset($oldStart, $target);
            $first = SitePlanner::where('site_id', $siteId)->orderBy('from')->orderBy('id')->firstOrFail();
            $firstFrom = $first->from->copy()->startOfDay();
            $movedFirst = $this->dates->shift($firstFrom, $offset);

            if ($movedFirst->lt(Carbon::today())) {
                $count = $this->tasks->moveSiteFrom($siteId, $oldStart->format('Y-m-d'), $offset);
                $this->tasks->moveTo((int)$first->id, $this->firstAvailableWorkday()->format('Y-m-d'));

                return $count + 1;
            }

            return $this->tasks->moveSiteFrom($siteId, $firstFrom->format('Y-m-d'), $offset);
        });

        $this->sendJobStartEmail($site, $target, $oldStart);

        return $moved;
    }

    public function allocateJob(int $siteId, int $supervisorId): void
    {
        $site = Site::findOrFail($siteId);
        $site->supervisor_id = $supervisorId;
        $site->status = 1;
        $site->save();
    }

    protected function validTargetDate(string $date): Carbon
    {
        $target = $this->dates->parse($date);

        if ($target->lt(Carbon::today())) {
            throw new DomainException('The Job Start cannot be moved before today.');
        }

        if (!$this->dates->isWorkDay($target)) {
            throw new DomainException('Choose a weekday that is not a public holiday.');
        }

        return $target;
    }

    protected function signedWorkdayOffset(Carbon $from, Carbon $to): int
    {
        $days = max(0, $this->dates->workDaysBetween($from, $to) - 1);

        return $to->gt($from) ? $days : -$days;
    }

    protected function presetDate(Carbon $start, int $offset): Carbon
    {
        $date = $this->dates->shift($start, $offset);

        if ($offset < 0 && $date->lte(Carbon::today())) {
            return $this->firstAvailableWorkday();
        }

        return $date;
    }

    protected function firstAvailableWorkday(): Carbon
    {
        $date = Carbon::today();

        while (!$this->dates->isWorkDay($date)) {
            $date->addDay();
        }

        return $date;
    }

    protected function sendJobStartEmail(Site $site, Carbon $newDate, ?Carbon $oldDate = null): void
    {
        try {
            $recipients = app()->environment('prod')
                ? $site->company->notificationsUsersType('site.jobstart')
                : [config('mail.email_dev')];

            $recipients = array_values(array_filter((array)$recipients));

            if ($recipients) {
                Mail::to($recipients)->send(new \App\Mail\Site\Jobstart(
                    $site,
                    $newDate->format('d/m/Y'),
                    $oldDate?->format('d/m/Y'),
                    $site->supervisorName,
                ));
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    protected function jobStartPresets(): array
    {
        return [
            ['entity_type' => 't', 'entity_id' => 31, 'task_id' => 264, 'offset' => -5, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 11, 'offset' => 0, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 21, 'task_id' => 200, 'offset' => 0, 'days' => 1],
            ['entity_type' => 'c', 'entity_id' => 9, 'task_id' => 116, 'offset' => 0, 'days' => 1],
            ['entity_type' => 'c', 'entity_id' => 118, 'task_id' => 107, 'offset' => 0, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 22, 'offset' => 1, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 4, 'offset' => 2, 'days' => 4],
            ['entity_type' => 't', 'entity_id' => 4, 'task_id' => 51, 'offset' => 2, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 8, 'task_id' => 86, 'offset' => 2, 'days' => 1],
            ['entity_type' => 'c', 'entity_id' => 359, 'task_id' => 183, 'offset' => 4, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 7, 'offset' => 5, 'days' => 4],
            ['entity_type' => 't', 'entity_id' => 21, 'task_id' => 224, 'offset' => 7, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 21, 'task_id' => 220, 'offset' => 8, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 24, 'offset' => 8, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 20, 'task_id' => 191, 'offset' => 9, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 9, 'task_id' => 100, 'offset' => 10, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 9, 'task_id' => 108, 'offset' => 11, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 21, 'task_id' => 221, 'offset' => 12, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 25, 'offset' => 12, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 10, 'offset' => 12, 'days' => 2],
            ['entity_type' => 't', 'entity_id' => 2, 'task_id' => 27, 'offset' => 12, 'days' => 1],
            ['entity_type' => 'c', 'entity_id' => 359, 'task_id' => 184, 'offset' => 13, 'days' => 1],
            ['entity_type' => 't', 'entity_id' => 21, 'task_id' => 198, 'offset' => 14, 'days' => 1],
        ];
    }
}
