<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Domain\AI\Models\AiSetting;
use App\Infrastructure\AI\AiProviderRegistry;

final class TestAiConnectionAction
{
    public function execute(?AiSetting $settings = null): void
    {
        $settings ??= AiSetting::current();
        app(AiProviderRegistry::class)->provider((string) $settings->provider)->testConnection($settings);
    }
}
