<?php

declare(strict_types=1);

namespace App\Domain\AI\Data;

final readonly class ProductContentSuggestion
{
    public function __construct(
        public ?string $name,
        public ?string $shortDescription,
        public ?string $description,
        public ?string $seoTitle,
        public ?string $seoDescription,
        public ?string $categorySlug,
    ) {}

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'short_description' => $this->shortDescription,
            'description' => $this->description,
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'category_slug' => $this->categorySlug,
        ];
    }
}
