<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Aguardando pagamento',
            self::APPROVED => 'Pagamento aprovado',
            self::FAILED => 'Pagamento não aprovado',
            self::CANCELLED => 'Pagamento cancelado',
            self::REFUNDED => 'Pagamento estornado',
        };
    }
}
