<?php

declare(strict_types=1);

use App\Infrastructure\Payments\FakePaymentGateway;

return [
    'default' => env('PAYMENT_GATEWAY', 'fake'),
    'gateways' => [
        'fake' => FakePaymentGateway::class,
    ],
];
