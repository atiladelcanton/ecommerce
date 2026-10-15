<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Exceptions;

use RuntimeException;

final class ShippingQuoteUnavailable extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct(match ($reason) {
            'missing_measurements' => 'Frete sob consulta: faltam informações de peso ou embalagem de um dos itens.',
            'carrier_rejected' => 'Frete sob consulta. Não há entrega automática disponível para o peso e as medidas deste carrinho.',
            default => 'Frete sob consulta. Não foi possível cotar a entrega automaticamente agora. Tente novamente ou solicite atendimento.',
        });
    }
}
