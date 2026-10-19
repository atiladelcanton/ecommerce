<?php

declare(strict_types=1);

namespace App\Domain\AI\Data;

final readonly class ProductContentRequest
{
    /** @param array<string, mixed> $current @param array<int, array{slug:string,name:string}> $categories */
    public function __construct(
        public string $mode,
        public ?string $brief,
        public array $current,
        public array $categories,
        public ?string $storeInstructions = null,
    ) {}
}
