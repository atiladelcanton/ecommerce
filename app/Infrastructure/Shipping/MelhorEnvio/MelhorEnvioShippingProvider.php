<?php

declare(strict_types=1);

namespace App\Infrastructure\Shipping\MelhorEnvio;

use App\Domain\Shipping\Contracts\ShipmentLabelProvider;
use App\Domain\Shipping\Contracts\ShipmentProvider;
use App\Domain\Shipping\Contracts\ShipmentTrackingProvider;
use App\Domain\Shipping\Contracts\ShippingCapabilitiesProvider;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Domain\Shipping\Data\ApiMoney;
use App\Domain\Shipping\Data\ShipmentCreateRequest;
use App\Domain\Shipping\Data\ShipmentCreateResult;
use App\Domain\Shipping\Data\ShipmentLabelFile;
use App\Domain\Shipping\Data\ShipmentTrackingResult;
use App\Domain\Shipping\Data\ShippingCapabilities;
use App\Domain\Shipping\Data\ShippingItem;
use App\Domain\Shipping\Data\ShippingOption;
use App\Domain\Shipping\Data\ShippingQuoteRequest;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\ShippingService;

final class MelhorEnvioShippingProvider implements ShipmentLabelProvider, ShipmentProvider, ShipmentTrackingProvider, ShippingCapabilitiesProvider, ShippingProvider
{
    public function __construct(private readonly MelhorEnvioClient $client) {}

    public function key(): string
    {
        return 'melhor_envio';
    }

    public function capabilities(): ShippingCapabilities
    {
        return new ShippingCapabilities(labels: true, automaticTracking: true);
    }

    public function quote(ShippingQuoteRequest $request): array
    {
        $integration = $this->client->integration();
        if (! $integration->is_enabled || ! $integration->access_token) {
            return [];
        }
        foreach ($request->items as $item) {
            foreach ([$item->weight, $item->height, $item->width, $item->length] as $measurement) {
                if (! is_string($measurement) || ! preg_match('/^\d+(?:\.\d+)?$/D', $measurement) || (float) $measurement <= 0) {
                    throw new \App\Domain\Shipping\Exceptions\ShippingQuoteUnavailable('missing_measurements');
                }
            }
        }
        $services = ShippingService::query()->with('carrier')->where('is_enabled', true)->where('is_available', true)
            ->whereHas('carrier', fn ($query) => $query->where('shipping_integration_id', $integration->id)->where('environment', $integration->environment)->where('is_enabled', true)->where('is_available', true))->get();
        if ($services->isEmpty()) {
            return [];
        }
        $options = [];
        $failed = false;
        foreach ($services as $service) {
            try {
                array_push($options, ...$this->quoteService($request, $integration, $service));
            } catch (\Throwable $exception) {
                $failed = true;
                \Illuminate\Support\Facades\Log::warning('Shipping service quote failed', ['provider' => $this->key(), 'endpoint' => '/api/v2/me/shipment/calculate', 'service_id' => $service->external_id, 'exception_type' => $exception::class]);
            }
        }
        if ($failed && $options !== []) {
            throw new \App\Domain\Shipping\Exceptions\PartialShippingQuoteFailure($options);
        }
        if ($options === []) {
            throw new \App\Domain\Shipping\Exceptions\ShippingQuoteUnavailable($failed || $services->isEmpty() ? 'provider_unavailable' : 'carrier_rejected');
        }

        return $options;
    }

    private function quoteService(ShippingQuoteRequest $request, \App\Domain\Shipping\Models\ShippingIntegration $integration, ShippingService $service): array
    {
        $options = [];
        $settings = $integration->configuration ?? [];
        $rules = $service->metadata ?? [];
        $insuranceRequired = (bool) ($rules['insurance_required'] ?? true);
        $insured = ($settings['insurance_mode'] ?? 'always') === 'always' || $insuranceRequired;
        $extras = [];
        foreach (['receipt', 'own_hand'] as $extra) {
            if (($rules['additional_services'][$extra] ?? false) === true) {
                $extras[$extra] = (bool) ($settings[$extra] ?? false);
            }
        }
        $payload = [
            'from' => ['postal_code' => $request->originPostalCode], 'to' => ['postal_code' => $request->destinationPostalCode],
            'services' => $service->external_id,
            'products' => array_map(fn (ShippingItem $item): array => [
                'id' => $item->lineId ?? (string) $item->productId, 'width' => (float) $item->width, 'height' => (float) $item->height,
                'length' => (float) $item->length, 'weight' => (float) $item->weight,
                'insurance_value' => new ApiMoney($insured ? $item->unitPriceCents : 0), 'quantity' => $item->quantity,
            ], $request->items),
        ];
        if ($extras !== []) {
            $payload['options'] = $extras;
        }
        $response = $this->client->post('/api/v2/me/shipment/calculate', $payload);
        $rows = array_is_list($response) ? $response : ($response['data'] ?? $response);
        if (isset($rows['id'])) {
            $rows = [$rows];
        }
        foreach ($rows as $row) {
            if (! is_array($row) || isset($row['error']) || (string) ($row['id'] ?? '') !== $service->external_id) {
                continue;
            }
            if (isset($row['company']['id']) && (string) $row['company']['id'] !== $service->carrier->external_id) {
                continue;
            }
            if (! isset($row['custom_price']) && ! isset($row['price'])) {
                continue;
            }
            $packages = [];
            foreach (($row['packages'] ?? []) as $package) {
                $packages[] = [
                    'dimensions' => $package['dimensions'] ?? [], 'weight' => $package['weight'] ?? null,
                    'price_cents' => ApiMoney::cents($package['price'] ?? '0'),
                    'insurance_cents' => ApiMoney::cents($package['insurance_value'] ?? '0'),
                    'products' => $package['products'] ?? [],
                ];
            }
            // An automatic option must carry the data needed for the later purchase.
            if ($service->fulfillment_mode === 'automatic' && $packages === []) {
                continue;
            }
            $range = $row['custom_delivery_range'] ?? $row['delivery_range'] ?? null;
            $range = is_array($range) && isset($range['min'], $range['max']) ? ['min' => (int) $range['min'], 'max' => (int) $range['max']] : null;
            $days = (int) ($row['custom_delivery_time'] ?? $row['delivery_time'] ?? 0);
            $description = $range ? $range['min'].' a '.$range['max'].' dias úteis' : ($days > 0 ? $days.' dias úteis' : 'Prazo a confirmar');
            $options[] = new ShippingOption(
                id: $this->key().':'.$service->external_id, name: $service->name,
                price: ApiMoney::cents($row['custom_price'] ?? $row['price'] ?? '0'), description: $description,
                provider: $this->key(), serviceExternalId: $service->external_id,
                carrierExternalId: $service->carrier->external_id, carrierName: $service->carrier->name,
                estimatedDays: $days ?: null, integrationId: $integration->id, environment: $integration->environment,
                estimatedRange: $range, packages: $packages, fulfillmentMode: $service->fulfillment_mode,
                metadata: [
                    'options' => $extras, 'insured' => $insured, 'label_format' => $settings['label_format'] ?? 'pdf',
                    'agency_id' => $service->carrier->default_shipping_agency_id,
                    'agency_required' => (bool) ($rules['agency_required'] ?? false),
                    'supports_agency' => (bool) ($rules['supports_agency'] ?? ($service->carrier->default_shipping_agency_id !== null)),
                    'grouped_volumes' => (bool) ($rules['has_grouped_volumes'] ?? $service->carrier->metadata['has_grouped_volumes'] ?? false),
                    'automatic_tracking' => $service->fulfillment_mode === 'automatic',
                    'products' => array_map(fn (ShippingItem $item): array => ['id' => $item->lineId ?? (string) $item->productId, 'name' => $item->name, 'quantity' => $item->quantity, 'unit_price_cents' => $item->unitPriceCents], $request->items),
                ],
            );
        }

        return $options;
    }

    public function createCart(ShipmentCreateRequest $request): ShipmentCreateResult
    {
        $metadata = $request->quoteSnapshot['metadata'] ?? [];
        $options = array_merge($metadata['options'] ?? [], [
            'platform' => 'SABA E-commerce', 'reminder' => 'Pedido '.$request->orderNumber,
            'insurance_value' => new ApiMoney($request->insuranceValueCents),
            'tags' => [['tag' => $request->orderNumber, 'url' => $request->orderUrl], ['tag' => 'saba-operation:'.$request->operationKey, 'url' => null]],
        ]);
        if ($request->invoiceKey) {
            $options['invoice'] = ['key' => $request->invoiceKey];
        }
        if ($request->dceKey) {
            $options['dce'] = ['key' => $request->dceKey];
        }
        $payload = ['service' => (int) $request->serviceExternalId, 'from' => $request->sender, 'to' => $request->recipient,
            'products' => $request->products, 'volumes' => $request->volumes, 'options' => $options];
        if ($request->agencyExternalId && (($metadata['supports_agency'] ?? false) || ($metadata['agency_required'] ?? false))) {
            $payload['agency'] = (int) $request->agencyExternalId;
        }
        $cart = $this->client->post('/api/v2/me/cart', $payload);
        if (! is_string($cart['id'] ?? null) || $cart['id'] === '') {
            throw new ShippingProviderFailed('O Melhor Envio não confirmou o identificador do envio. Concilie antes de repetir.', uncertain: true);
        }

        return new ShipmentCreateResult($cart['id'], isset($cart['price']) ? ApiMoney::cents($cart['price']) : null, metadata: ['protocol' => $cart['protocol'] ?? null]);
    }

    public function purchase(string $externalId): void
    {
        $this->client->post('/api/v2/me/shipment/checkout', ['orders' => [$externalId]]);
    }

    public function generateLabel(string $externalId): void
    {
        $this->client->post('/api/v2/me/shipment/generate', ['orders' => [$externalId]]);
    }

    public function printLabel(string $externalId, string $format = 'pdf'): string
    {
        $printed = $this->client->post('/api/v2/me/shipment/print', ['mode' => 'public', 'orders' => [$externalId]]);
        $url = $printed['url'] ?? null;
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            throw new ShippingProviderFailed('A impressão ainda não está disponível. Tente novamente em instantes.');
        }

        return $url;
    }

    public function downloadLabel(string $externalId, string $format): ShipmentLabelFile
    {
        if (! in_array($format, ['pdf', 'zpl', 'jpeg'], true)) {
            throw new ShippingProviderFailed('Formato de etiqueta inválido.');
        }
        $response = $this->client->download('/api/v2/me/imprimir/'.$format.'/'.rawurlencode($externalId));

        return new ShipmentLabelFile($response->body(), match ($format) {
            'pdf' => 'application/pdf', 'jpeg' => 'image/jpeg', default => 'application/octet-stream'
        }, 'etiqueta-'.preg_replace('/[^a-zA-Z0-9_-]/', '', $externalId).'.'.$format);
    }

    public function canCancel(string $externalId): bool
    {
        $response = $this->client->post('/api/v2/me/shipment/cancellable', ['orders' => [$externalId]]);
        $row = $response[$externalId] ?? $response[0] ?? $response;

        return is_bool($row) ? $row : (bool) ($row['cancellable'] ?? false);
    }

    public function cancel(string $externalId, string $reason): void
    {
        $this->client->post('/api/v2/me/shipment/cancel', ['order' => ['id' => $externalId, 'reason_id' => '2', 'description' => $reason]]);
    }

    public function inspect(string $externalId): ShipmentTrackingResult
    {
        $row = $this->client->get('/api/v2/me/orders/'.rawurlencode($externalId));

        return new ShipmentTrackingResult(strtolower((string) ($row['status'] ?? 'unknown')), metadata: ['tags' => $row['tags'] ?? [], 'service' => $row['service_id'] ?? $row['service']['id'] ?? null]);
    }

    public function track(string $externalId): ShipmentTrackingResult
    {
        $response = $this->client->post('/api/v2/me/shipment/tracking', ['orders' => [$externalId]]);
        $row = $response[$externalId] ?? $response[0] ?? $response;

        return new ShipmentTrackingResult(strtolower((string) ($row['status'] ?? 'unknown')),
            isset($row['tracking']) ? (string) $row['tracking'] : null, $row['tracking_url'] ?? null);
    }
}
