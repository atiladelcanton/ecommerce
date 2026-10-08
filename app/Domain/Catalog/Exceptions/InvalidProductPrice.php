<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Exceptions;

use InvalidArgumentException;

final class InvalidProductPrice extends InvalidArgumentException
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }
}
