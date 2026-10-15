<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Catalog\ManageOrderInventoryAction;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Shipping\Enums\ShipmentStatus;
use App\Domain\Shipping\Enums\ShippingPricingStatus;
use App\Domain\Shipping\OrderShipping;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransitionOrderStatusAction
{
    public function execute(
        Order $order,
        OrderStatus $to,
        ?int $userId = null,
        ?string $description = null,
        ?string $carrier = null,
        ?string $trackingCode = null,
        ?string $trackingUrl = null,
        bool $fromProvider = false,
        bool $contingency = false,
    ): Order {
        return DB::transaction(function () use ($order, $to, $userId, $description, $carrier, $trackingCode, $trackingUrl, $fromProvider, $contingency): Order {
            /** @var Order $locked */
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            $from = $locked->status;

            if ($from === $to) {
                return $locked->refresh();
            }

            if (! in_array($to, $from->allowedTransitions(), true)) {
                throw ValidationException::withMessages([
                    'status' => "Não é possível mover o pedido de {$from->label()} para {$to->label()}.",
                ]);
            }

            if ($to === OrderStatus::CANCELLED && $locked->shipments()->whereNull('cancelled_at')->exists()) {
                throw ValidationException::withMessages(['status' => 'Cancele ou concilie os envios pendentes antes de cancelar comercialmente o pedido.']);
            }
            if ($to !== OrderStatus::CANCELLED && $locked->shipping_pricing_status === ShippingPricingStatus::AWAITING_QUOTE) {
                throw ValidationException::withMessages(['status' => 'Defina e confirme o frete antes de avançar este pedido.']);
            }
            if ($to === OrderStatus::PAID && $locked->shipping_pricing_status !== null && $locked->payment_status !== PaymentStatus::APPROVED) {
                throw ValidationException::withMessages(['status' => 'O cliente precisa confirmar o frete e o pagamento.']);
            }
            if ($to === OrderStatus::CANCELLED) {
                app(ManageOrderInventoryAction::class)->cancel($locked);
            }
            $mode = OrderShipping::mode($locked);
            if ($contingency && ! filled($description)) {
                throw ValidationException::withMessages(['description' => 'Informe a justificativa da contingência.']);
            }
            if ($mode === 'automatic' && in_array($to, [OrderStatus::SHIPPED, OrderStatus::DELIVERED], true) && ! $fromProvider && ! $contingency) {
                throw ValidationException::withMessages(['status' => 'Este envio é atualizado pela transportadora. Use a ação de contingência para uma intervenção manual.']);
            }
            if ($from === OrderStatus::READY_TO_SHIP && $to === OrderStatus::PROCESSING) {
                if (! OrderShipping::currentShipments($locked)->contains(fn ($shipment) => $shipment->status === ShipmentStatus::CANCELLED)) {
                    throw ValidationException::withMessages(['status' => 'A preparação só pode ser reaberta para substituir uma etiqueta cancelada.']);
                }
                $locked->ready_for_shipping_at = null;
            }
            if ($to === OrderStatus::READY_FOR_SHIPPING) {
                if (OrderShipping::mode($locked) === 'pickup') {
                    throw ValidationException::withMessages(['status' => 'Pedidos com retirada devem ser marcados como prontos para retirada.']);
                }
                if ($mode === 'automatic' && ! $fromProvider && (OrderShipping::currentShipments($locked)->isEmpty() || ! OrderShipping::currentShipments($locked)->every(fn ($s) => $s->generated_at || $s->posted_at || $s->delivered_at))) {
                    throw ValidationException::withMessages(['status' => 'Gere todas as etiquetas antes de marcar o pedido como pronto para envio.']);
                }
                $locked->ready_for_shipping_at = now();
            }

            if ($to === OrderStatus::SHIPPED) {
                if (OrderShipping::mode($locked) === 'pickup') {
                    throw ValidationException::withMessages(['status' => 'Pedidos com retirada devem ser marcados como prontos para retirada.']);
                }
                if (! $fromProvider) {
                    if (! filled($carrier)) {
                        throw ValidationException::withMessages(['carrier' => 'Informe a transportadora.']);
                    }

                }
                if (filled($carrier)) {
                    $locked->carrier = trim((string) $carrier);
                }
                if (filled($trackingCode)) {
                    $locked->tracking_code = trim((string) $trackingCode);
                }
                if (filled($trackingUrl)) {
                    $locked->tracking_url = trim((string) $trackingUrl);
                }
                $locked->shipped_at = now();
            }

            if ($to === OrderStatus::READY_FOR_PICKUP && OrderShipping::mode($locked) !== 'pickup') {
                throw ValidationException::withMessages(['status' => 'Somente pedidos de retirada podem ser marcados como prontos para retirada.']);
            }

            if ($to === OrderStatus::DELIVERED) {
                $locked->delivered_at = now();
            }

            $locked->status = $to;
            $locked->save();

            $locked->statusHistories()->create([
                'user_id' => $userId,
                'from_status' => $from,
                'to_status' => $to,
                'description' => ($contingency ? 'Contingência administrativa: ' : '').($description ?: $this->defaultDescription($to)),
            ]);

            return $locked->refresh();
        }, 3);
    }

    private function defaultDescription(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::PAID => 'Pagamento aprovado.',
            OrderStatus::PROCESSING => 'Pedido enviado para preparação e separação.',
            OrderStatus::READY_FOR_SHIPPING => 'Etiqueta gerada. Pedido pronto para postagem.',
            OrderStatus::SHIPPED => 'Pedido postado e enviado para entrega.',
            OrderStatus::READY_FOR_PICKUP => 'Pedido pronto para retirada.',
            OrderStatus::DELIVERED => 'Pedido concluído e entregue.',
            OrderStatus::CANCELLED => 'Pedido cancelado.',
            OrderStatus::PENDING => 'Pedido recebido.',
        };
    }
}
