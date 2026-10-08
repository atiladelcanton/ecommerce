<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

enum ProductAvailability: string
{
    case IMMEDIATE = 'immediate';
    case ON_DEMAND = 'on_demand';
    case UNAVAILABLE = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::IMMEDIATE => 'Disponibilidade imediata',
            self::ON_DEMAND => 'Sob encomenda',
            self::UNAVAILABLE => 'Indisponível',
        };
    }
}
