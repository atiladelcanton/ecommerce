<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\AI\Contracts\AiContentProvider;
use App\Infrastructure\AI\Groq\GroqContentProvider;
use InvalidArgumentException;

final class AiProviderRegistry
{
    /** @return array<string, array{label:string,implemented:bool}> */
    public function definitions(): array
    {
        return [
            'groq' => ['label' => 'Groq', 'implemented' => true],
            'openai' => ['label' => 'OpenAI', 'implemented' => false],
            'gemini' => ['label' => 'Google Gemini', 'implemented' => false],
            'openrouter' => ['label' => 'OpenRouter', 'implemented' => false],
            'ollama' => ['label' => 'Ollama local', 'implemented' => false],
        ];
    }

    public function provider(string $key): AiContentProvider
    {
        return match ($key) {
            'groq' => app(GroqContentProvider::class),
            default => throw new InvalidArgumentException('O provedor de IA selecionado ainda não está implementado.'),
        };
    }

    /** @return array<string, string> */
    public function implementedOptions(): array
    {
        return collect($this->definitions())
            ->filter(fn (array $definition): bool => $definition['implemented'])
            ->mapWithKeys(fn (array $definition, string $key): array => [$key => $definition['label']])
            ->all();
    }
}
