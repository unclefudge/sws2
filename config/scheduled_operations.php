<?php

return [
    /*
    | Keep "legacy" during the first deployment. "shadow" records what would
    | run without executing it, while "live" uses the independent queue jobs.
    */
    'mode' => env('SCHEDULED_OPERATIONS', 'legacy'),

    // Forge's existing database worker listens to this queue by default.
    'queue' => env('SCHEDULED_OPERATIONS_QUEUE', 'default'),

    // A failed job is retried by the worker before the monitor emails this address.
    'alert_email' => env('SCHEDULED_OPERATIONS_ALERT_EMAIL', env('EMAIL_DEV')),
    'failure_alert_delay_minutes' => (int) env('SCHEDULED_OPERATIONS_ALERT_DELAY', 10),

    // Prevent very noisy legacy reports from making the database grow without limit.
    'max_output_bytes' => (int) env('SCHEDULED_OPERATIONS_MAX_OUTPUT_BYTES', 131072),
    'history_days' => (int) env('SCHEDULED_OPERATIONS_HISTORY_DAYS', 90),
    'email_history_per_task' => (int) env('SCHEDULED_OPERATIONS_EMAIL_HISTORY', 10),
];
