<?php

return [

    'client_id' => env('PAYPAL_CLIENT_ID', ''),
    'secret' => env('PAYPAL_SECRET', ''),
    'url' => env('PAYPAL_MODE') == 'sandbox' ? 'https://api.sandbox.paypal.com/v1/' : 'https://api.paypal.com/v1/',
    'settings' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'http.ConnectionTimeOut' => 30,
        'log.LogEnabled' => true,
        'log.FileName' => storage_path() . '/logs/paypal.log',
        'log.LogLevel' => 'ERROR'

    ],
];
