<?php

declare(strict_types=1);

namespace App\Domain\Shipping\Enums;

enum ShipmentDocumentType: string
{
    case INVOICE = 'invoice';
    case CONTENT_DECLARATION = 'content_declaration';

    public function label(): string
    {
        return match ($this) {
            self::INVOICE => 'Nota Fiscal (NF-e)',
            self::CONTENT_DECLARATION => 'Declaração de conteúdo',
        };
    }

    public function isInvoice(): bool
    {
        return $this === self::INVOICE;
    }

    public function isContentDeclaration(): bool
    {
        return $this === self::CONTENT_DECLARATION;
    }
}
