<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioClient;

final class TestMelhorEnvioConnectionAction
{
    public function __construct(private readonly MelhorEnvioClient $client) {}

    public function execute(): void
    {
        $this->client->get('/api/v2/me/shipment/services');
        $this->client->integration()->update(['last_tested_at' => now()]);
    }
}
