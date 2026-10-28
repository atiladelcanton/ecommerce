<?php

declare(strict_types=1);

namespace App\Domain\AI\Data;

final readonly class BlogGenerationResult
{
    public function __construct(
        public BlogContentSuggestion $suggestion,
        public int $inputTokens = 0,
        public int $outputTokens = 0,
        public int $totalTokens = 0,
        public ?int $durationMs = null,
    ) {}
}
