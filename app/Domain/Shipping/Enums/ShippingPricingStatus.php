<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Enums;

enum ShippingPricingStatus: string
{
    case AWAITING_QUOTE = 'awaiting_quote';
    case QUOTED = 'quoted';
    case CONFIRMED = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::AWAITING_QUOTE => 'Aguardando cotação do frete',
            self::QUOTED => 'Frete cotado — aguardando confirmação',
            self::CONFIRMED => 'Frete confirmado',
        };
    }
}
