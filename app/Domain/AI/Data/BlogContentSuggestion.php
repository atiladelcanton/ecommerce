<?php

declare(strict_types=1);

namespace App\Domain\AI\Data;

final readonly class BlogContentSuggestion
{
    public function __construct(
        public ?string $title,
        public ?string $excerpt,
        public ?string $content,
        public ?string $seoTitle,
        public ?string $seoDescription,
    ) {}
}
