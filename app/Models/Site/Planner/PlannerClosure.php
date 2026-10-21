<?php

namespace App\Models\Site\Planner;

/** Planner availability only; never used as statutory public holiday data. */
class PlannerClosure extends PublicHoliday
{
    protected $table = 'planner_closures';
}
