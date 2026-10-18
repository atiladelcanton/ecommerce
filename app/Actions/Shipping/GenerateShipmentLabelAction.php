<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Contracts\ShipmentLabelProvider;
use App\Domain\Shipping\Contracts\ShipmentProvider;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\Shipment;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Domain\Shipping\OrderShipping;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GenerateShipmentLabelAction
{
    public function __construct(private readonly ShippingProviderRegistry $providers, private readonly BuildShipmentRequestAction $requests) {}

    public function execute(
        Order $order,
        ?string $invoiceKey = null,
        ?string $dceKey = null,
        ?int $userId = null,
        ?int $agencyId = null,
        ShipmentDocumentType|string $documentType = ShipmentDocumentType::INVOICE,
    ): Shipment {
        $documentType = $documentType instanceof ShipmentDocumentType
            ? $documentType
            : ShipmentDocumentType::tryFrom($documentType) ?? ShipmentDocumentType::INVOICE;

        try {
            return Cache::lock('shipping-label-order:'.$order->id, 600)->block(3, fn (): Shipment => $this->generate(
                $order,
                $invoiceKey,
                $dceKey,
                $userId,
                $agencyId,
                $documentType,
            ));
        } catch (LockTimeoutException $lockTimeoutException) {
            throw ValidationException::withMessages(['shipping' => 'Este envio já está sendo preparado. Aguarde e atualize a página.']);
        }
    }

    private function generate(
        Order $order,
        ?string $invoiceKey,
        ?string $dceKey,
        ?int $userId,
        ?int $agencyId,
        ShipmentDocumentType $documentType,
    ): Shipment {
        $order->refresh()->load(['items', 'address']);
        $existing = OrderShipping::currentShipments($order);
        if ($existing->isNotEmpty() && $existing->every(fn (Shipment $s) => in_array($s->status, [ShipmentStatus::LABEL_GENERATED, ShipmentStatus::POSTED, ShipmentStatus::DELIVERED], true))) {
            return $existing->first();
        }
        if ($existing->contains(fn (Shipment $s) => str_starts_with((string) $s->fulfillment_key, 'legacy-'))) {
            throw ValidationException::withMessages(['shipping' => 'Este pedido possui um envio antigo que precisa ser verificado antes de preparar novos volumes.']);
        }
        if ($order->status !== OrderStatus::PROCESSING || $order->payment_status !== PaymentStatus::APPROVED) {
            throw ValidationException::withMessages(['shipping' => 'A etiqueta exige pagamento aprovado e pedido em preparação.']);
        }

        $provider = $order->shipping_provider ? $this->providers->get($order->shipping_provider) : null;
        if (OrderShipping::mode($order) !== 'automatic' || ! $provider instanceof ShipmentProvider || ! $provider instanceof ShipmentLabelProvider) {
            throw ValidationException::withMessages(['shipping' => 'Este envio utiliza preparação manual ou retirada, sem compra automática de etiqueta.']);
        }

        $snapshot = OrderShipping::snapshot($order);
        $integration = ShippingIntegration::query()->find($snapshot['integration_id'] ?? $order->shipping_integration_id);
        if (! $integration || $integration->provider !== $order->shipping_provider || ! $integration->access_token || $integration->environment !== ($snapshot['environment'] ?? null)) {
            throw ValidationException::withMessages(['shipping' => 'Conecte a integração no mesmo ambiente do frete contratado.']);
        }

        $invoiceKey = $documentType->isInvoice() && filled($invoiceKey) ? (preg_replace('/\D/', '', $invoiceKey) ?: null) : null;
        $dceKey = $documentType->isContentDeclaration() && filled($dceKey) ? (preg_replace('/\D/', '', $dceKey) ?: null) : null;

        $packages = $snapshot['packages'] ?? $snapshot['metadata']['packages'] ?? [];
        $agencyId ??= $snapshot['metadata']['agency_id'] ?? null;
        $groups = ($snapshot['metadata']['grouped_volumes'] ?? false) ? [$packages] : array_map(fn ($package) => [$package], $packages);
        if ($groups === []) {
            throw ValidationException::withMessages(['shipping' => 'O snapshot não contém volumes para preparar o envio.']);
        }

        // Validate every package and the selected fiscal mode before the first
        // financially relevant request to the shipping provider.
        if ($existing->isEmpty()) {
            foreach ($groups as $group) {
                $this->requests->execute(
                    $order,
                    $snapshot,
                    $group,
                    $invoiceKey,
                    $dceKey,
                    $agencyId,
                    documentType: $documentType,
                );
            }
        }

        $storedDocumentType = $order->shipping_document_type instanceof ShipmentDocumentType
            ? $order->shipping_document_type
            : ShipmentDocumentType::tryFrom((string) $order->shipping_document_type);

        if ($existing->contains(fn (Shipment $s) => filled($s->external_id)) && (
            $storedDocumentType !== $documentType
            || $order->invoice_key !== $invoiceKey
            || $order->dce_key !== $dceKey
        )) {
            throw ValidationException::withMessages(['shipping_document_type' => 'O tipo de documento e os dados fiscais já enviados ao provedor não podem ser alterados nesta tentativa.']);
        }

        DB::transaction(function () use ($order, $snapshot, $groups, $documentType, $invoiceKey, $dceKey, $agencyId): void {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->status !== OrderStatus::PROCESSING) {
                throw ValidationException::withMessages(['shipping' => 'O estado do pedido foi alterado. Atualize a página.']);
            }

            $locked->update([
                'shipping_document_type' => $documentType->value,
                'invoice_key' => $invoiceKey,
                'dce_key' => $dceKey,
            ]);

            $remaining = $locked->shipping_cents;
            foreach ($groups as $index => $group) {
                $key = 'package-'.$index;
                $current = $locked->shipments()->where('fulfillment_key', $key)->orderByDesc('attempt')->first();
                $price = count($groups) === 1
                    ? $locked->shipping_cents
                    : ($index === array_key_last($groups) ? $remaining : min($remaining, (int) ($group[0]['price_cents'] ?? 0)));
                $remaining -= $price;
                if ($current) {
                    continue;
                }

                $locked->shipments()->create([
                    'provider' => $locked->shipping_provider,
                    'shipping_integration_id' => $snapshot['integration_id'],
                    'environment' => $snapshot['environment'],
                    'operation_key' => (string) Str::uuid(),
                    'fulfillment_key' => $key,
                    'attempt' => 1,
                    'shipping_agency_id' => $agencyId,
                    'carrier_external_id' => $snapshot['carrier_external_id'] ?? null,
                    'carrier_name' => $snapshot['carrier_name'] ?? null,
                    'service_external_id' => $snapshot['service_external_id'],
                    'service_name' => $snapshot['name'],
                    'status' => ShipmentStatus::PENDING,
                    'customer_price_cents' => $price,
                    'packages' => $group,
                    'quote_snapshot' => $snapshot,
                    'label_format' => $snapshot['metadata']['label_format'] ?? 'pdf',
                ]);
            }
        });

        $order->refresh()->load(['items', 'address']);
        $currentShipments = OrderShipping::currentShipments($order);
        $requests = [];
        foreach ($currentShipments as $pending) {
            if ($pending->status === ShipmentStatus::PENDING) {
                $requests[$pending->id] = $this->requests->execute(
                    $order,
                    $pending->quote_snapshot,
                    $pending->packages,
                    $order->invoice_key,
                    $order->dce_key,
                    $pending->shipping_agency_id,
                    $pending->operation_key,
                    $order->shipping_document_type ?? ShipmentDocumentType::INVOICE,
                );
            }
        }

        foreach ($currentShipments as $shipment) {
            if ($shipment->status === ShipmentStatus::CANCELLED) {
                throw ValidationException::withMessages(['shipping' => 'Prepare uma nova tentativa para o envio cancelado antes de continuar.']);
            }

            try {
                if ($shipment->status === ShipmentStatus::PENDING) {
                    $this->claim($shipment, 'cart_in_progress');
                    $result = $provider->createCart($requests[$shipment->id]);
                    $shipment->update([
                        'external_id' => $result->externalId,
                        'provider_price_cents' => $result->providerPriceCents,
                        'status' => ShipmentStatus::CART_CREATED,
                        'operation_phase' => null,
                        'metadata' => $result->metadata,
                    ]);
                }
                if ($shipment->status === ShipmentStatus::CART_CREATED) {
                    $this->claim($shipment, 'purchase_in_progress');
                    $provider->purchase($shipment->external_id);
                    Shipment::query()->whereKey($shipment->id)->where('status', ShipmentStatus::CART_CREATED->value)->update([
                        'status' => ShipmentStatus::PURCHASED->value,
                        'operation_phase' => null,
                    ]);
                    $shipment->refresh();
                }
                if ($shipment->status === ShipmentStatus::PURCHASED) {
                    $this->claim($shipment, 'generate_in_progress', allowResume: true);
                    $provider->generateLabel($shipment->external_id);
                    // A webhook may have advanced the shipment while the request was running.
                    Shipment::query()->whereKey($shipment->id)->where('status', ShipmentStatus::PURCHASED->value)->update([
                        'status' => ShipmentStatus::LABEL_GENERATED->value,
                        'generated_at' => now(),
                        'operation_phase' => null,
                    ]);
                }
            } catch (ShippingProviderFailed $exception) {
                if (! $exception->uncertain || $shipment->operation_phase === 'generate_in_progress') {
                    $shipment->update(['operation_phase' => null]);
                }
                throw ValidationException::withMessages([
                    'shipping' => $exception->uncertain && $shipment->operation_phase
                        ? 'Não foi possível confirmar a última operação no Melhor Envio. Para evitar uma cobrança duplicada, o envio foi pausado. Use “Verificar envio” no pedido antes de tentar novamente.'
                        : $exception->getMessage(),
                ]);
            }
        }

        app(ProjectOrderShippingStatusAction::class)->execute($order);

        return OrderShipping::currentShipments($order)->first();
    }

    private function claim(Shipment $shipment, string $phase, bool $allowResume = false): void
    {
        DB::transaction(function () use ($shipment, $phase, $allowResume): void {
            $locked = Shipment::query()->lockForUpdate()->findOrFail($shipment->id);
            if ($locked->operation_phase && ! ($allowResume && $locked->operation_phase === $phase)) {
                throw ValidationException::withMessages(['shipping' => 'Não foi possível confirmar a última operação no Melhor Envio. Para evitar uma cobrança duplicada, use “Verificar envio” antes de continuar.']);
            }
            $locked->update(['operation_phase' => $phase, 'operation_started_at' => now()]);
            $shipment->setRawAttributes($locked->getAttributes(), true);
        });
    }
}
