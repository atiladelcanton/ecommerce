<?php

declare(strict_types=1);

namespace App\Domain\AI\Data;

final readonly class BlogContentRequest
{
    /** @param array<string, string|null> $current */
    public function __construct(
        public string $mode,
        public ?string $brief,
        public array $current,
        public string $tone,
        public string $length,
        public ?string $storeInstructions = null,
    ) {}
}
