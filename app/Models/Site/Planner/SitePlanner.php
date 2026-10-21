<?php

namespace App\Models\Site\Planner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SitePlanner extends Model
{

    //use SoftDeletes;

    protected $table = 'site_planner';
    protected $fillable = [
        'site_id', 'from', 'to', 'days', 'entity_type', 'entity_id', 'task_id', 'weekend',
        'created_by', 'updated_by', 'created_at', 'updated_at'
    ];
    protected $casts = ['from' => 'datetime', 'to' => 'datetime'];

    /**
     * A SitePlanner belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SitePlanner belongs to a task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo('App\Models\Site\Planner\Task');
    }

    /**
     * A SitePlanner belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * A SitePlanner belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function company()
    {
        return ($this->entity_type == 'c') ? $this->belongsTo('App\Models\Company\Company', 'entity_id') : null;
    }

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->site->company;
    }

    public function setWeekendAttribute($value): void
    {
        $this->attributes['weekend'] =
            filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    /**
     * Company Tasks for given site + date
     *
     * @return array
     */
    /*
    public function companyTasksForSiteOnDate($company_id, $site_id, $date)
    {
        $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $weekend = ($carbon_date->isWeekend() ? 1 : 0);
        $planner = SitePlanner::where('site_id', $site_id)
            ->whereDate('from', '<=', $date)->whereDate('to', '>=', $date)
            ->where('weekend', $weekend)->get();

        $tasks = [];
        foreach ($planner as $plan) {
            if ($plan->task_id) {
                $task = Task::find($plan->task_id);
                $tasks[$task->id] = ($task) ? $task->name : 'Task Unassigned';
            }

        }

        return $tasks;
    }*/


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

        static::saving(function ($plan) {
            if ($plan->exists && !$plan->isDirty(['from', 'to', 'days', 'weekend'])) {
                return;
            }
            $dates = app(\App\Services\Planner\PlannerDateService::class);
            $from = $dates->parse($plan->from);
            $to = $dates->parse($plan->to ?? $plan->from);
            // Preserve completed history. Future calendar changes are reviewed separately.
            if ($from->lt(\Carbon\Carbon::today()) && $to->lt(\Carbon\Carbon::today())) {
                return;
            }
            if ($dates->isBlocked($from) && $from->gte(\Carbon\Carbon::today())) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'from' => 'Tasks cannot start on a non-working date.',
                ]);
            }
            if (!$plan->weekend) {
                if ($from->isWeekend()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'from' => 'Choose a working weekday for this task.',
                    ]);
                }
                $plan->days = max(1, (int)$plan->days);
                $plan->to = $dates->endDate($from, $plan->days)->toDateTimeString();
            } else {
                if ($to->lt($from)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'to' => 'The finish date cannot be before the start date.',
                    ]);
                }
                // Preserve the legacy weekend duration rules, but never override closures.
                $day = $from->copy()->max(\Carbon\Carbon::today());
                while ($day->lte($to)) {
                    if ($dates->isBlocked($day)) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'from' => 'This weekend task crosses a non-working date. Move or split it first.',
                        ]);
                    }
                    $day->addDay();
                }
            }
        });

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