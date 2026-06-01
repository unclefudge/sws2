<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'hia' => [
        'base_url' => env('HIA_BASE_URL'),
        'username' => env('HIA_USERNAME'),
        'password' => env('HIA_PASSWORD'),
        'client_id' => env('HIA_CLIENT_ID'),
        'client_secret' => env('HIA_CLIENT_SECRET'),
        'api_version' => env('HIA_API_VERSION', '1.0.0'),
        'scope' => env('HIA_SCOPE', 'BASIC,COL,DIRECTAUTH'),
    ],

    'zoho' => [
        'client_id' => env('ZOHO_CLIENT_ID'),
        'client_secret' => env('ZOHO_CLIENT_SECRET'),
        'crm_url' => env('ZOHO_CRM_URL', 'https://www.zohoapis.com.au/crm/v8'),
        'crm_refresh_token' => env('ZOHO_CRM_REFRESH_TOKEN'),
        'connect_url' => env('ZOHO_CONNECT_URL', 'https://connect.zoho.com'),
        'connect_refresh_token' => env('ZOHO_CONNECT_REFRESH_TOKEN'),
        'connect_scope_id' => env('ZOHO_CONNECT_SCOPE_ID'),
        'accounts_url' => env('ZOHO_ACCOUNTS_URL', 'https://accounts.zoho.com'),
    ],
];
