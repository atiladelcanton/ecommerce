<?php

declare(strict_types=1);

namespace App\Domain\AI\Contracts;

use App\Domain\AI\Data\AiGenerationResult;
use App\Domain\AI\Data\BlogContentRequest;
use App\Domain\AI\Data\BlogGenerationResult;
use App\Domain\AI\Data\ProductContentRequest;
use App\Domain\AI\Models\AiSetting;

interface AiContentProvider
{
    public function key(): string;

    public function label(): string;

    public function testConnection(AiSetting $settings): void;

    public function generateProductContent(ProductContentRequest $request, AiSetting $settings): AiGenerationResult;

    public function generateBlogContent(BlogContentRequest $request, AiSetting $settings): BlogGenerationResult;
}
