<?php

declare(strict_types=1);

namespace App\Infrastructure\Shipping;

use App\Domain\Shipping\Contracts\ShippingCapabilitiesProvider;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ShippingCapabilities;
use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

final class ShippingProviderRegistry
{
    public function capabilities(string $key): ShippingCapabilities
    {
        $provider = $this->get($key);

        return $provider instanceof ShippingCapabilitiesProvider
            ? $provider->capabilities() : new ShippingCapabilities;
    }

    public function definitions(): array
    {
        return ['melhor_envio' => 'Melhor Envio', 'superfrete' => 'SuperFrete', 'correios' => 'Correios', 'local_pickup' => 'Retirada na loja'];
    }

    public function isImplemented(string $key): bool
    {
        $class = config('shipping.providers.'.$key);

        return is_string($class) && is_a($class, ShippingProvider::class, true);
    }

    /** @return list<ShippingProvider> */
    public function enabled(): array
    {
        if (! Schema::hasTable('shipping_integrations')) {
            return [];
        }

        $rows = ShippingIntegration::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $providers = [];
        foreach ($rows as $row) {
            if ($row->provider === 'fake' && ! app()->runningUnitTests()) {
                continue;
            }
            $class = config('shipping.providers.'.$row->provider);
            if (! is_string($class) || ! class_exists($class)) {
                continue;
            }
            $provider = app($class);
            if ($provider instanceof ShippingProvider) {
                $providers[] = $provider;
            }
        }

        return $providers;
    }

    public function get(string $key): ShippingProvider
    {
        $class = config('shipping.providers.'.$key);
        if (! is_string($class) || ! class_exists($class)) {
            throw new InvalidArgumentException("Unsupported shipping provider [{$key}].");
        }

        $provider = app($class);
        if (! $provider instanceof ShippingProvider) {
            throw new InvalidArgumentException("Invalid shipping provider [{$key}].");
        }

        return $provider;
    }
}
