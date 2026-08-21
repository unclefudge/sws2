<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Weekly Planner version
    |--------------------------------------------------------------------------
    |
    | Keep "legacy" as the safe default while the Livewire planner is being
    | tested. The preview URL always displays the Livewire version regardless
    | of this setting.
    |
    | PLANNER_WEEKLY_VERSION=legacy
    | PLANNER_WEEKLY_VERSION=livewire
    |
    */
    'weekly_version' => env('PLANNER_WEEKLY_VERSION', 'legacy'),
];
