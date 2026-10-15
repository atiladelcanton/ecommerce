<?php

declare(strict_types=1);

namespace App\Domain\Order\Enums;

enum OrderStatus: string
{
    case RECEIVED = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case READY_TO_SHIP = 'ready_for_shipping';
    case SHIPPED = 'shipped';
    case READY_FOR_PICKUP = 'ready_for_pickup';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public const PENDING = self::RECEIVED;

    public const READY_FOR_SHIPPING = self::READY_TO_SHIP;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pedido recebido',
            self::PAID => 'Pedido pago',
            self::PROCESSING => 'Em preparação',
            self::READY_FOR_SHIPPING => 'Pronto para envio',
            self::SHIPPED => 'Enviado',
            self::READY_FOR_PICKUP => 'Pronto para retirada',
            self::DELIVERED => 'Entregue',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PAID => 'success',
            self::PROCESSING => 'warning',
            self::READY_FOR_SHIPPING => 'info',
            self::SHIPPED => 'info',
            self::READY_FOR_PICKUP => 'primary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::PAID, self::CANCELLED],
            self::PAID => [self::PROCESSING, self::CANCELLED],
            // SHIPPED remains available for non-integrated/manual carriers.
            self::PROCESSING => [self::READY_FOR_SHIPPING, self::SHIPPED, self::READY_FOR_PICKUP, self::CANCELLED],
            self::READY_FOR_SHIPPING => [self::SHIPPED, self::PROCESSING, self::CANCELLED],
            self::SHIPPED, self::READY_FOR_PICKUP => [self::DELIVERED],
            self::DELIVERED, self::CANCELLED => [],
        };
    }
}
