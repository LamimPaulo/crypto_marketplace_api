<?php

return [
    'gateway' => [
        'user' => env('GATEWAY_USER'),
        'wallet' => [
            'BTC' => env('GATEWAY_BTC_WALLET'),
            'XRP' => env('GATEWAY_XRP_WALLET'),
            'ETH' => env('GATEWAY_ETH_WALLET'),
            'LTC' => env('GATEWAY_LTC_WALLET'),
            'DASH' => env('GATEWAY_DASH_WALLET'),
        ]
    ]
];


