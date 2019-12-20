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
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],
    'passport' => [
        'login_endpoint' => env('PASSPORT_LOGIN_ENDPOINT'),
        'client_id' => env('PASSPORT_CLIENT_ID'),
        'client_secret' => env('PASSPORT_CLIENT_SECRET'),
        'client_admin_id' => env('PASSPORT_ADMIN_ID'),
        'client_admin_secret' => env('PASSPORT_ADMIN_SECRET'),
    ],
    'token_mail_navi' => [
        'url' => env('NAVI_API_URL'),
        'token' => env('NAVI_API_TOKEN'),
        'cl' => env('NAVI_API_CL'),
    ],

    'offscreen' => [
        'BTC' => env('OFFSCREEN_BTC'),
        'BCH' => env('OFFSCREEN_BCH'),
        'LTC' => env('OFFSCREEN_LTC'),
        'DASH' => env('OFFSCREEN_DASH'),
        'LQX' => env('OFFSCREEN_LQX'),
    ],
    'gateway' => [
        'BTC' => env('OFFSCREEN_BTC'),
        'BCH' => env('OFFSCREEN_BCH'),
        'LTC' => env('OFFSCREEN_LTC'),
        'DASH' => env('OFFSCREEN_DASH'),
        'LQX' => env('GATEWAY_LQX'),
    ],
    'recaptcha' => [
        'key' => env('GOOGLE_RECAPTCHA_KEY'),
        'secret' => env('GOOGLE_RECAPTCHA_SECRET')
    ],
    'marketcap' => [
        'key' => env('MARKETCAP_DASH_KEY'),
        'DASH' => [
            'email' => env('MARKETCAP_DASH_EMAIL'),
            'key' => env('MARKETCAP_DASH_KEY'),
        ],
    ],
    'masternode' => [
        'api' => env("MASTERNODES_LQX")
    ],
    'slushpool' => [
        'key' => env('SLUSH_KEY'),
        'stats' => 'https://slushpool.com/stats/json/btc',
        'profile' => 'https://slushpool.com/accounts/profile/json/btc',
        'workers' => 'https://slushpool.com/accounts/workers/json/btc'
    ],

];
