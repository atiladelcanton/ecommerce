<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Enums;

enum ShipmentStatus: string
{
    case QUOTED = 'quoted';
    case PENDING = 'pending';
    case CART_CREATED = 'cart_created';
    case PURCHASED = 'purchased';
    case LABEL_GENERATED = 'label_generated';
    case POSTED = 'posted';
    case DELIVERED = 'delivered';
    case UNDELIVERED = 'undelivered';
    case PAUSED = 'paused';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::QUOTED => 'Cotado',
            self::PENDING => 'Pendente',
            self::CART_CREATED => 'No carrinho da transportadora',
            self::PURCHASED => 'Frete comprado',
            self::LABEL_GENERATED => 'Etiqueta gerada',
            self::POSTED => 'Postado',
            self::DELIVERED => 'Entregue',
            self::UNDELIVERED => 'Não entregue',
            self::PAUSED => 'Pausado',
            self::SUSPENDED => 'Suspenso',
            self::CANCELLED => 'Cancelado',
        };
    }
}
