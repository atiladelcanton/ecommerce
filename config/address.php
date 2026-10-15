<?php

declare(strict_types=1);

return [
    'provider' => env('ADDRESS_LOOKUP_PROVIDER', 'viacep'),
    'cache_ttl' => 86400,
    'providers' => [
        'viacep' => [
            'url' => 'https://viacep.com.br/ws',
            'timeout' => 4,
            'connect_timeout' => 3,
        ],
        // Register a BrasilAPI adapter here when that integration is implemented.
    ],
];
