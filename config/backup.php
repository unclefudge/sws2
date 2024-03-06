<?php

return [

    'backup' => [

        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => config('app.name'),

        'source' => [

            'files' => [

                /*
                 * The list of directories and files that will be included in the backup.
                 */
                'include' => [
                    base_path('public/filebank/support'),
                    base_path('public/filebank/user'),
                    base_path('public/filebank/company'),
                    //base_path('public/filebank/site/*/hazard'),
                    //base_path('public/filebank/site/*/maintenance'),
                    //base_path('public/filebank/site/*/inspection'),
                    base_path('public/filebank/site'),
                    base_path('public/filebank/incident'),
                    base_path('public/filebank/inspection'),
                    base_path('public/filebank/form'),
                    base_path('public/filebank/whs'),
                ],

                /*
                 * These directories and files will be excluded from the backup.
                 *
                 * Directories used by the backup process will automatically be excluded.
                 */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path(),
                    base_path('source'),
                    base_path('public/filebank/site/*/docs'),
                ],

                /*
                 * Determines if symlinks should be followed.
                 */
                'follow_links' => false,
            ],

            /*
             * The names of the connections to the databases that should be backed up
             * MySQL, PostgreSQL, SQLite and Mongo databases are supported.
             */
            'databases' => [
                'mysql',
            ],
        ],

        /*
         * The database dump can be gzipped to decrease diskspace usage.
         */
        'gzip_database_dump' => false,

        'destination' => [

            /*
             * The filename prefix used for the backup zip file.
             */
            'filename_prefix' => '',

            /*
             * The disk names on which the backups will be stored.
             */
            'disks' => [
                'local',
                //'do',
            ],
        ],
    ],

    /*
     * You can get notified when specific events occur. Out of the box you can use 'mail' and 'slack'.
     * For Slack you need to install guzzlehttp/guzzle.
     *
     * You can also use your own notification classes, just make sure the class is named after one of
     * the `Spatie\Backup\Events` classes.
     */
    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => [''],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [''],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [''],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'support@openhands.com.au',
        ],

        'slack' => [
            'webhook_url' => '',

            /*
             * If this is set to null the default channel of the webhook will be used.
             */
            'channel' => null,
        ],
    ],

    /*
     * Here you can specify which backups should be monitored.
     * If a backup does not meet the specified requirements the
     * UnHealthyBackupWasFound event will be fired.
     */
    'monitor_backups' => [
        [
            'name' => config('app.name'),
            'disks' => ['local'],
            'newest_backups_should_not_be_older_than_days' => 1,
            'storage_used_may_not_be_higher_than_megabytes' => 5000,
        ],
    ],

    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups. The default strategy
         * will keep all backups for a certain amount of days. After that period only
         * a daily backup will be kept. After that period only weekly backups will
         * be kept and so on.
         *
         * No matter how you configure it the default strategy will never
         * delete the newest backup.
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [

            /*
             * The number of days for which backups must be kept.
             */
            'keep_all_backups_for_days' => 7,

            /*
             * The number of days for which daily backups must be kept.
             */
            'keep_daily_backups_for_days' => 10,

            /*
             * The number of weeks for which one weekly backup must be kept.
             */
            'keep_weekly_backups_for_weeks' => 5,

            /*
             * The number of months for which one monthly backup must be kept.
             */
            'keep_monthly_backups_for_months' => 12,

            /*
             * The number of years for which one yearly backup must be kept.
             */
            'keep_yearly_backups_for_years' => 3,

            /*
             * After cleaning up the backups remove the oldest backup until
             * this amount of megabytes has been reached.
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 200000,   // 200Gb
        ],
    ],
];
