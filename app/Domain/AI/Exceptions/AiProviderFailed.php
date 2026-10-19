<?php

declare(strict_types=1);

namespace App\Domain\AI\Exceptions;

use RuntimeException;
use Throwable;

final class AiProviderFailed extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $errorCode = null,
        public readonly ?int $status = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
