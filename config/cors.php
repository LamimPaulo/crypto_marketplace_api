<?php
/*
|--------------------------------------------------------------------------
| Laravel CORS
|--------------------------------------------------------------------------
|
| allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
| to accept any value.
|
*/


if (env('APP_ENV') == 'local') {
    return [

        'supportsCredentials' => false,
        'allowedOrigins' => ['*'],
        'allowedHeaders' => ['*'],
        'allowedMethods' => ['*'], // ex: ['GET', 'POST', 'PUT',  'DELETE']
        'exposedHeaders' => [],
        'maxAge' => 0,

    ];
}
return [

    'supportsCredentials' => false,
    'allowedOrigins' => ['liquidex.com.br'],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'], // ex: ['GET', 'POST', 'PUT',  'DELETE']
    'exposedHeaders' => [],
    'maxAge' => 0,

];