<?php

return [
    'default' => env('BROADCAST_DRIVER', 'pusher'),
    'use_insecure_pusher_tls' => env('PUSHER_INSECURE_TLS', env('APP_ENV') === 'local'),

    'connections' => [
        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'encrypted' => true,
                'host' => env('PUSHER_HOST') ?: 'api-' . env('PUSHER_APP_CLUSTER', 'mt1') . '.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'curl_options' => (bool) env('PUSHER_INSECURE_TLS', env('APP_ENV') === 'local')
                    ? [
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_SSL_VERIFYPEER => 0,
                    ]
                    : [
                        CURLOPT_SSL_VERIFYHOST => 2,
                        CURLOPT_SSL_VERIFYPEER => 1,
                    ],
                'client_options' => [
                    'verify' => ! (bool) env('PUSHER_INSECURE_TLS', env('APP_ENV') === 'local'),
                ],
            ],
        ],
    ],
];
