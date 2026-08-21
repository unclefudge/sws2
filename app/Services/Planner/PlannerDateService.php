<?php

namespace App\Services\Planner;

use App\Models\Site\Planner\PublicHoliday;
use Carbon\Carbon;
use InvalidArgumentException;

class PlannerDateService
{
    protected ?array $holidays = null;

    public function holidays(): array
    {
        if ($this->holidays === null) {
            $this->holidays = PublicHoliday::where('status', 1)
                ->get()
                ->mapWithKeys(fn (PublicHoliday $holiday) => [$holiday->date->format('Y-m-d') => $holiday->name])
                ->all();
        }

        return $this->holidays;
    }

    public function parse(string|Carbon $date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date->copy()->startOfDay();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', substr($date, 0, 10))->startOfDay();
        } catch (\Throwable) {
            throw new InvalidArgumentException('The planner date is invalid.');
        }
    }

    public function isWorkDay(string|Carbon $date): bool
    {
        $date = $this->parse($date);

        return !$date->isWeekend() && !array_key_exists($date->format('Y-m-d'), $this->holidays());
    }

    public function shift(string|Carbon $date, int $workDays): Carbon
    {
        $date = $this->parse($date);

        if ($workDays === 0) {
            return $date;
        }

        $direction = $workDays > 0 ? 1 : -1;
        $remaining = abs($workDays);

        while ($remaining > 0) {
            $direction > 0 ? $date->addDay() : $date->subDay();

            if ($this->isWorkDay($date)) {
                $remaining--;
            }
        }

        return $date;
    }

    public function endDate(string|Carbon $from, int $days): Carbon
    {
        return $this->shift($from, max(1, $days) - 1);
    }

    public function workDaysBetween(string|Carbon $from, string|Carbon $to): int
    {
        $from = $this->parse($from);
        $to = $this->parse($to);

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $days = 0;

        while ($from->lte($to)) {
            if ($this->isWorkDay($from)) {
                $days++;
            }

            $from->addDay();
        }

        return $days;
    }
}
