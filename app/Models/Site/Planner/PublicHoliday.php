<?php

namespace App\Models\Site\Planner;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PublicHoliday extends Model
{

    protected $table = 'public_holidays';
    protected $fillable = [
        'name', 'type', 'date', 'end_date', 'status', 'created_by', 'updated_by'];
    protected $casts = ['date' => 'datetime', 'end_date' => 'date'];

    public const TYPES = [
        'public' => 'Public holiday',
        'shutdown' => 'Company shutdown',
        'other' => 'Other closure',
    ];

    public static function activeDates(): array
    {
        // Cache only on the current request, never across users or requests.
        $attributes = request()->attributes;
        $key = 'planner.calendar.' . (new static())->getTable();
        if (!$attributes->has($key)) {
            $attributes->set($key, static::expandDates(static::where('status', 1)->orderBy('date')->orderBy('id')->get()));
        }

        return $attributes->get($key);
    }

    /** Expand inclusive ranges, merging labels when entries overlap. */
    public static function expandDates(iterable $entries): array
    {
        $labels = [];
        foreach ($entries as $entry) {
            if ((int) $entry->status !== 1) {
                continue;
            }
            $day = $entry->date->copy()->startOfDay();
            $end = ($entry->end_date ?? $entry->date)->copy()->startOfDay();
            while ($day->lte($end)) {
                $labels[$day->format('Y-m-d')][] = $entry->name;
                $day->addDay();
            }
        }
        $dates = [];
        foreach ($labels as $date => $names) {
            $dates[$date] = implode(' / ', array_unique($names));
        }
        ksort($dates);

        return $dates;
    }


    /**
     * Display records last update_by + date
     *
     * @return string
     */
    public function displayUpdatedBy()
    {
        $user = User::findOrFail($this->updated_by);
        return '<span style="font-weight: 400">Last modified: </span>' . $this->updated_at->diffForHumans() . ' &nbsp; ' .
            '<span style="font-weight: 400">By:</span> ' . $user->fullname;
    }

    /**
     * The "booting" method of the model.
     *
     * Overrides parent function
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        $invalidateCalendar = function () {
            request()->attributes->remove('planner.calendar.public_holidays');
            request()->attributes->remove('planner.calendar.planner_closures');
            app(\App\Services\Planner\PlannerDateService::class)->clearCache();
        };
        static::saving(function ($entry) {
            if ($entry->getTable() === 'public_holidays' && ($entry->type ?? 'public') !== 'public') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'type' => 'Company shutdowns must be saved in the separate planner closure calendar.',
                ]);
            }
        });
        static::saved($invalidateCalendar);
        static::deleted($invalidateCalendar);

        if (Auth::check()) {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = Auth::user()->id;
                $table->updated_by = Auth::user()->id;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = Auth::user()->id;
            });
        }
    }

}

