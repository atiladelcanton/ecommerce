<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class UpdateShippingIntegrationAction
{
    /** @param array<string, mixed> $attributes */
    public function execute(string $provider, array $attributes): ShippingIntegration
    {
        return DB::transaction(function () use ($provider, $attributes): ShippingIntegration {
            $class = config('shipping.providers.'.$provider);
            if (! is_string($class) || ! is_a($class, ShippingProvider::class, true)) {
                throw ValidationException::withMessages(['provider' => 'Este meio de entrega ainda não está implementado.']);
            }
            $integration = ShippingIntegration::forProvider($provider);
            $integration = ShippingIntegration::query()->lockForUpdate()->findOrFail($integration->id);

            $credentials = $integration->credentials ?? [];
            foreach (($attributes['credentials'] ?? []) as $key => $value) {
                if ($value !== null && trim((string) $value) !== '') {
                    $credentials[$key] = trim((string) $value);
                }
            }

            $environment = $attributes['environment'] ?? $integration->environment;
            if (! in_array($environment, ['sandbox', 'production'], true)) {
                throw ValidationException::withMessages(['environment' => 'Selecione um ambiente válido.']);
            }
            if ($environment !== $integration->environment || ($credentials['client_id'] ?? null) !== ($integration->credentials['client_id'] ?? null) || ($credentials['client_secret'] ?? null) !== ($integration->credentials['client_secret'] ?? null)) {
                $integration->forceFill(['access_token' => null, 'refresh_token' => null, 'token_expires_at' => null, 'connected_at' => null]);
            }
            $configuration = array_merge($integration->configuration ?? [], $attributes['configuration'] ?? []);
            Validator::make($configuration, [
                'application_name' => ['sometimes', 'required', 'string', 'max:120'],
                'technical_email' => ['sometimes', 'nullable', 'email', 'max:255'],
                'insurance_mode' => ['sometimes', 'in:always,required,never'],
                'label_format' => ['sometimes', 'in:pdf,zpl,jpeg'],
                'ready_days' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:365'],
                'label' => ['sometimes', 'required', 'string', 'max:120'],
                'address' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'instructions' => ['sometimes', 'nullable', 'string', 'max:2000'],
            ])->validate();
            $integration->fill([
                'is_enabled' => (bool) ($attributes['is_enabled'] ?? $integration->is_enabled),
                'environment' => $environment,
                'sort_order' => (int) ($attributes['sort_order'] ?? $integration->sort_order ?? 100),
                'configuration' => $configuration,
                'credentials' => $credentials,
            ])->save();

            Cache::forever('shipping-configuration-revision', (string) Str::uuid());

            return $integration->refresh();
        });
    }
}
