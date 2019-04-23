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
        'XMR' => env('OFFSCREEN_XMR'),
    ],
    'recaptcha' => [
        'key' => env('GOOGLE_RECAPTCHA_KEY'),
        'secret' => env('GOOGLE_RECAPTCHA_SECRET')
    ],
    'binance' => [
        'secret' => env('BINANCE_SECRET'),
        'key' => env('BINANCE_KEY'),
    ],
    'marketcap' => [
        'key' => 'e5ea3914-506d-472a-8cc6-679b99bb3b21',
        'DASH' => [
            'email' => 'dash.marketcap@navi.inf.br',
            'key' => '4a5c2963-00a5-4d13-a630-779b781a9bab',
        ],
        'XMR' => [
            'email' => 'xmr.marketcap@navi.inf.br',
            'key' => 'ecbe6f64-e5fd-4fe8-b0f6-451b09330be2',
        ],
    ],
    'masternode' => [
        'api' => 'https://masternodes.pro/api/v1/',
        'key' => env("MASTERNODE_KEY")
    ]

];
