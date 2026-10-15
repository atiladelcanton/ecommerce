<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class DisconnectShippingIntegrationAction
{
    public function execute(string $provider): void
    {
        ShippingIntegration::forProvider($provider)->update(['is_enabled' => false, 'access_token' => null, 'refresh_token' => null, 'token_expires_at' => null, 'connected_at' => null]);
        Cache::forever('shipping-configuration-revision', (string) Str::uuid());
    }
}
