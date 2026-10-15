<?php

declare(strict_types=1);

use App\Infrastructure\Shipping\LocalPickupProvider;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioShippingProvider;

return [
    'default' => env('SHIPPING_PROVIDER'),

    'providers' => [
        'manual' => \App\Infrastructure\Shipping\ManualShippingProvider::class,
        'melhor_envio' => MelhorEnvioShippingProvider::class,
        'local_pickup' => LocalPickupProvider::class,
    ],

    'melhor_envio' => [
        // Optional explicit callback. Useful behind reverse proxies/tunnels when the public URL
        // differs from Laravel's detected request URL. It must exactly match the app registered
        // in Melhor Envio.
        'callback_url' => env('MELHOR_ENVIO_CALLBACK_URL'),

        'scopes' => [
            'cart-write',
            'orders-read',
            'shipping-calculate',
            'shipping-cancel',
            'shipping-checkout',
            'shipping-companies',
            'shipping-generate',
            'shipping-print',
            'shipping-tracking',
            'ecommerce-shipping',
        ],
    ],
];
