<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | FileBank settings
    |--------------------------------------------------------------------------
    |
    | filebank_default_disk: where NEW uploads go (spaces or local)
    | filebank_fallback_disk: where OLD files may still exist during migration
    | filebank_temp_prefixes: always keep these on local disk
    |
    */

    'filebank_default_disk' => env('FILEBANK_DISK', 'filebank_local'),
    'filebank_fallback_disk' => env('FILEBANK_FALLBACK_DISK', 'filebank_local'),
    'filebank_temp_prefixes' => array_filter(explode(',', env('FILEBANK_TEMP_PREFIXES', 'tmp/,log/'))),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        'filebank_local' => [
            'driver' => 'local',
            'root' => public_path('filebank'), // IMPORTANT: store outside /public long-term, but for now match your legacy location:
            'throw' => false,
        ],

        'filebank_spaces' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'region' => env('DO_SPACES_REGION', 'syd1'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'visibility' => 'private',
            'use_path_style_endpoint' => false,
            'throw' => false,
        ],

        'backup_spaces' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'region' => env('DO_SPACES_REGION', 'syd1'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'root' => 'backups',
            'visibility' => 'private',
        ],

    ],

];
