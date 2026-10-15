<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Models\ShippingIntegration;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Validation\ValidationException;

final class ToggleShippingIntegrationAction
{
    public function __construct(private readonly ShippingProviderRegistry $providers, private readonly UpdateShippingIntegrationAction $update) {}

    public function execute(string $provider): void
    {
        try {
            $capabilities = $this->providers->capabilities($provider);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['provider' => 'Este meio de entrega ainda não está implementado.']);
        }
        $integration = ShippingIntegration::forProvider($provider);
        if (! $integration->is_enabled && $capabilities->pickup && ! filled($integration->configuration['address'] ?? null)) {
            throw ValidationException::withMessages(['address' => 'Configure o endereço da retirada antes de ativar.']);
        }
        $this->update->execute($provider, ['is_enabled' => ! $integration->is_enabled, 'environment' => $integration->environment]);
    }
}
