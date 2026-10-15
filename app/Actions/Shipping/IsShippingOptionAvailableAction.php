<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Domain\Shipping\Models\ShippingService;

final class IsShippingOptionAvailableAction
{
    public function execute(ShippingOption $option): bool
    {
        if ($option->integrationId === null) {
            return true;
        }
        $integration = ShippingIntegration::query()->whereKey($option->integrationId)->where('provider', $option->provider)->where('is_enabled', true)->first();
        if (! $integration || ($option->environment && $option->environment !== $integration->environment)) {
            return false;
        }
        if ($option->carrierExternalId === null) {
            return true;
        }

        return ShippingService::query()->where('external_id', $option->serviceExternalId)->where('is_enabled', true)->where('is_available', true)
            ->whereHas('carrier', fn ($query) => $query->where('external_id', $option->carrierExternalId)->where('shipping_integration_id', $integration->id)->where('environment', $integration->environment)->where('is_enabled', true)->where('is_available', true))->exists();
    }
}
