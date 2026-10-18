<?php

declare(strict_types=1);

namespace App\Actions\Shipping;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Shipping\Contracts\ShipmentOperationLookupProvider;
use App\Domain\Shipping\Contracts\ShipmentProvider;
use App\Domain\Shipping\Enums\ShipmentDocumentType;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\Shipment;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use Illuminate\Validation\ValidationException;

/**
 * User-facing recovery for an operation whose HTTP outcome was uncertain.
 *
 * The safety rule is intentionally strict: we only continue the original
 * preparation after the provider proves which remote shipment belongs to the
 * persisted operation key. A missing/ambiguous match never triggers a new
 * financially relevant request.
 */
final class VerifyShipmentAction
{
    public function __construct(
        private readonly ShippingProviderRegistry $providers,
        private readonly AssertShipmentConnectionAction $connection,
        private readonly ReconcileShipmentAction $reconcile,
        private readonly GenerateShipmentLabelAction $generate,
    ) {}

    public function execute(Shipment $shipment, ?int $userId = null): Shipment
    {
        $shipment->refresh();

        if (! $shipment->operation_phase) {
            return $shipment;
        }

        $this->connection->execute($shipment);
        $provider = $this->providers->get($shipment->provider);
        if (! $provider instanceof ShipmentProvider) {
            throw ValidationException::withMessages([
                'shipping' => 'Este meio de entrega não oferece verificação automática desta operação.',
            ]);
        }

        $externalId = $shipment->external_id;

        if (! $externalId) {
            if (! $provider instanceof ShipmentOperationLookupProvider || ! $shipment->operation_key) {
                throw ValidationException::withMessages([
                    'shipping' => 'Ainda não foi possível confirmar o envio automaticamente. Nenhuma nova cobrança será feita. Tente novamente em alguns instantes.',
                ]);
            }

            try {
                $match = $provider->findByOperation($shipment->operation_key, (string) $shipment->service_external_id);
            } catch (ShippingProviderFailed $exception) {
                throw ValidationException::withMessages(['shipping' => $exception->getMessage()]);
            }

            if (! $match) {
                throw ValidationException::withMessages([
                    'shipping' => 'Ainda não encontramos no Melhor Envio uma operação correspondente a este pedido. Nenhuma nova cobrança será feita. Aguarde alguns instantes e clique em “Verificar envio” novamente.',
                ]);
            }

            $externalId = $match->externalId;
        }

        $shipment = $this->reconcile->execute($shipment, $externalId);

        // An uncertain checkout is special: while Melhor Envio still reports
        // `pending`, payment may simply be processing. Retrying checkout here
        // could duplicate a charge, so we keep the lock until a definitive
        // provider state (released/generated/posted/...) is observed.
        if ($shipment->operation_phase === 'purchase_in_progress') {
            throw ValidationException::withMessages([
                'shipping' => 'O envio foi encontrado, mas o Melhor Envio ainda não confirmou o pagamento do frete. Nenhuma nova compra será feita. Aguarde alguns minutos e clique em “Verificar envio” novamente.',
            ]);
        }

        if ($shipment->operation_phase) {
            throw ValidationException::withMessages([
                'shipping' => 'O envio foi localizado, mas o Melhor Envio ainda não confirmou o resultado da última operação. Nenhuma nova cobrança será feita. Tente verificar novamente em alguns instantes.',
            ]);
        }

        $shipment->refresh();
        $order = $shipment->order()->with(['items', 'address'])->firstOrFail();

        // If we safely recovered the cart or a paid label, continue the exact
        // operation the administrator had already requested. The generator is
        // resumable/idempotent and will never recreate an existing cart.
        if ($order->status === OrderStatus::PROCESSING
            && in_array($shipment->status, [ShipmentStatus::CART_CREATED, ShipmentStatus::PURCHASED], true)) {
            $documentType = $order->shipping_document_type instanceof ShipmentDocumentType
                ? $order->shipping_document_type
                : ShipmentDocumentType::tryFrom((string) $order->shipping_document_type) ?? ShipmentDocumentType::INVOICE;

            if ($documentType->isContentDeclaration() || filled($order->invoice_key)) {
                return $this->generate->execute(
                    $order,
                    $order->invoice_key,
                    $order->dce_key,
                    $userId,
                    $shipment->shipping_agency_id,
                    $documentType,
                )->refresh();
            }
        }

        return $shipment->refresh();
    }
}
