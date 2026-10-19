<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Domain\AI\Data\ProductContentRequest;
use App\Domain\AI\Data\ProductContentSuggestion;
use App\Domain\AI\Exceptions\AiProviderFailed;
use App\Domain\AI\Models\AiSetting;
use App\Domain\AI\Models\AiUsageLog;
use App\Domain\Catalog\Models\Category;
use App\Infrastructure\AI\AiProviderRegistry;
use App\Support\Catalog\ProductDescription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

final class GenerateProductContentAction
{
    /**
     * @param array<string, mixed> $current
     * @return array<string, mixed>
     */
    public function execute(string $mode, ?string $brief, array $current, ?int $productId = null): array
    {
        $validated = Validator::make([
            'mode' => $mode,
            'brief' => $brief,
        ], [
            'mode' => ['required', 'in:generate,improve'],
            'brief' => ['nullable', 'string', 'max:12000'],
        ])->validate();

        $settings = AiSetting::current();
        if (! $settings->isConfigured()) {
            throw ValidationException::withMessages(['ai' => 'Configure e ative a Inteligência Artificial antes de usar o assistente.']);
        }
        $this->assertMonthlyLimit($settings);

        $hasCurrentContent = collect(['name', 'short_description', 'description'])
            ->contains(fn (string $field): bool => filled($current[$field] ?? null));
        if (blank($validated['brief']) && ! $hasCurrentContent) {
            throw ValidationException::withMessages(['brief' => 'Descreva o produto ou preencha algum texto do cadastro antes de gerar.']);
        }

        $categories = Category::query()->active()->ordered()->get(['name', 'slug']);
        $request = new ProductContentRequest(
            mode: (string) $validated['mode'],
            brief: filled($validated['brief']) ? trim((string) $validated['brief']) : null,
            current: $this->safeCurrentData($current),
            categories: $categories->map(fn (Category $category): array => ['slug' => $category->slug, 'name' => $category->name])->all(),
            storeInstructions: $settings->store_instructions,
        );

        $provider = app(AiProviderRegistry::class)->provider((string) $settings->provider);

        try {
            $result = $provider->generateProductContent($request, $settings);
            $this->logUsage($settings, $result->inputTokens, $result->outputTokens, $result->totalTokens, $result->durationMs, true, null, $productId);
        } catch (AiProviderFailed $exception) {
            $this->logUsage($settings, 0, 0, 0, null, false, $exception->errorCode, $productId);
            throw $exception;
        } catch (Throwable $exception) {
            $this->logUsage($settings, 0, 0, 0, null, false, 'unexpected_error', $productId);
            report($exception);
            throw new AiProviderFailed('Não foi possível concluir a geração com IA. Tente novamente.', 'unexpected_error', previous: $exception);
        }

        return $this->normalizeSuggestion($result->suggestion);
    }

    private function assertMonthlyLimit(AiSetting $settings): void
    {
        $limit = $settings->monthly_request_limit;
        if ($limit === null) {
            return;
        }

        $used = AiUsageLog::query()
            ->where('provider', $settings->provider)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        if ($used >= $limit) {
            throw ValidationException::withMessages(['ai' => 'O limite mensal de gerações de IA configurado para a loja foi atingido.']);
        }
    }

    /** @param array<string, mixed> $current @return array<string, mixed> */
    private function safeCurrentData(array $current): array
    {
        return [
            'name' => $this->string($current['name'] ?? null),
            'short_description' => $this->string($current['short_description'] ?? null),
            'description' => ProductDescription::text($current['description'] ?? null),
            'seo_title' => $this->string($current['seo_title'] ?? null),
            'seo_description' => $this->string($current['seo_description'] ?? null),
            'sku' => $this->string($current['sku'] ?? null),
            'category' => $this->string($current['category'] ?? null),
            'variants' => is_array($current['variants'] ?? null) ? $current['variants'] : [],
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeSuggestion(ProductContentSuggestion $suggestion): array
    {
        $category = filled($suggestion->categorySlug)
            ? Category::query()->where('slug', $suggestion->categorySlug)->first(['id', 'name', 'slug'])
            : null;

        return [
            'name' => $this->limit($suggestion->name, 255),
            'short_description' => $this->limit($suggestion->shortDescription, 3000),
            'description' => filled($suggestion->description) ? ProductDescription::html($suggestion->description) : null,
            'seo_title' => $this->limit($suggestion->seoTitle, 255),
            'seo_description' => $this->limit($suggestion->seoDescription, 5000),
            'category_id' => $category?->id,
            'category_name' => $category?->name,
        ];
    }

    private function logUsage(AiSetting $settings, int $input, int $output, int $total, ?int $duration, bool $successful, ?string $errorCode, ?int $productId): void
    {
        AiUsageLog::query()->create([
            'provider' => (string) $settings->provider,
            'model' => (string) $settings->model,
            'action' => 'product_content',
            'product_id' => $productId,
            'user_id' => Auth::guard('web')->id(),
            'input_tokens' => max(0, $input),
            'output_tokens' => max(0, $output),
            'total_tokens' => max(0, $total),
            'duration_ms' => $duration,
            'successful' => $successful,
            'error_code' => $errorCode,
        ]);
    }

    private function string(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function limit(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr(trim($value), 0, $length);
    }
}
