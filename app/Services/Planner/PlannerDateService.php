<?php

namespace App\Services\Planner;

use App\Models\Site\Planner\PublicHoliday;
use App\Models\Site\Planner\PlannerClosure;
use Carbon\Carbon;
use InvalidArgumentException;

class PlannerDateService
{
    protected ?array $holidays = null;

    public function holidays(): array
    {
        if ($this->holidays === null) {
            $this->holidays = $this->mergeCalendars(PublicHoliday::activeDates(), PlannerClosure::activeDates());
        }

        return $this->holidays;
    }

    /** Only planner code calls this combined calendar. */
    public function mergeCalendars(array $publicHolidays, array $closures): array
    {
        $dates = $publicHolidays;
        foreach ($closures as $date => $name) {
            $dates[$date] = isset($dates[$date]) ? $dates[$date] . ' / ' . $name : $name;
        }
        ksort($dates);

        return $dates;
    }

    public function clearCache(): void
    {
        $this->holidays = null;
    }

    /** An isolated calendar for previews; never replace the live calendar. */
    public function withHolidays(array $holidays): self
    {
        $copy = clone $this;
        $copy->holidays = $holidays;

        return $copy;
    }

    public function isBlocked(string|Carbon $date): bool
    {
        return array_key_exists($this->parse($date)->format('Y-m-d'), $this->holidays());
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
