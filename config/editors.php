<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Toolbox Talk rich text editor
    |--------------------------------------------------------------------------
    |
    | Keep "ckeditor" as the production-safe default while Tiptap is being
    | evaluated. Switch an environment to Tiptap with:
    |
    | TOOLBOX_RICH_TEXT_EDITOR=tiptap
    |
    */
    'toolbox' => env('TOOLBOX_RICH_TEXT_EDITOR', 'ckeditor'),

    /*
    |--------------------------------------------------------------------------
    | Planner version
    |--------------------------------------------------------------------------
    |
    | Keep "legacy" as the safe default while the Livewire planners are being
    | tested. Preview URLs always display Livewire regardless of this setting.
    |
    | PLANNER=legacy
    | PLANNER=livewire
    |
    */
    'planner' => env('PLANNER', 'legacy'),
];
