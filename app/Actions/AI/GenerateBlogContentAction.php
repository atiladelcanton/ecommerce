<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Domain\AI\Data\BlogContentRequest;
use App\Domain\AI\Exceptions\AiProviderFailed;
use App\Domain\AI\Models\AiSetting;
use App\Domain\AI\Models\AiUsageLog;
use App\Infrastructure\AI\AiProviderRegistry;
use App\Support\Catalog\ProductDescription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

final class GenerateBlogContentAction
{
    /** @param array<string, mixed> $current @return array<string, string|null> */
    public function execute(string $mode, ?string $brief, array $current, string $tone = 'didático', string $length = 'médio'): array
    {
        abort_unless(Auth::guard('web')->check(), 403);
        $validated = Validator::make(compact('mode', 'brief', 'tone', 'length'), [
            'mode' => ['required', 'in:generate,improve'],
            'brief' => ['nullable', 'string', 'max:12000'],
            'tone' => ['required', 'in:didático,informativo,institucional,descontraído'],
            'length' => ['required', 'in:curto,médio,longo'],
        ])->validate();
        $safeCurrent = [
            'title' => $this->limited($current['title'] ?? null, 255),
            'excerpt' => $this->limited($current['excerpt'] ?? null, 2000),
            'content' => $this->limited(ProductDescription::text($current['content'] ?? null), 20000),
            'category' => $this->limited($current['category'] ?? null, 100),
            'seo_title' => $this->limited($current['seo_title'] ?? null, 255),
            'seo_description' => $this->limited($current['seo_description'] ?? null, 500),
        ];
        if (blank($validated['brief']) && blank($safeCurrent['title']) && blank($safeCurrent['content']) && blank($safeCurrent['excerpt'])) {
            throw ValidationException::withMessages(['brief' => 'Informe o tema do artigo ou preencha um título ou texto antes de solicitar a IA.']);
        }
        if ($mode === 'improve' && blank($safeCurrent['content']) && blank($safeCurrent['excerpt'])) {
            throw ValidationException::withMessages(['content' => 'Para melhorar uma postagem, escreva primeiro um resumo ou conteúdo no editor.']);
        }

        $settings = AiSetting::current();
        if (! $settings->isConfigured()) {
            throw ValidationException::withMessages(['ai' => 'Ative e configure a Inteligência Artificial antes de usar o assistente.']);
        }
        if ($settings->monthly_request_limit !== null) {
            $used = AiUsageLog::query()
                ->where('provider', $settings->provider)
                ->where('created_at', '>=', now()->startOfMonth())->count();
            if ($used >= $settings->monthly_request_limit) {
                throw ValidationException::withMessages(['ai' => 'O limite mensal de gerações de IA da loja foi atingido.']);
            }
        }

        try {
            $result = app(AiProviderRegistry::class)->provider((string) $settings->provider)->generateBlogContent(
                new BlogContentRequest($mode, $validated['brief'] ?: null, $safeCurrent, $tone, $length, $settings->store_instructions),
                $settings,
            );
            $suggestion = $result->suggestion;
            $fields = [
                'title' => $this->limited($suggestion->title, 255),
                'excerpt' => $this->limited($suggestion->excerpt, 2000),
                'content' => filled($suggestion->content) ? ProductDescription::html($suggestion->content) : null,
                'seo_title' => $this->limited($suggestion->seoTitle, 255),
                'seo_description' => $this->limited($suggestion->seoDescription, 500),
            ];
            if (blank(ProductDescription::text($fields['content']))) {
                throw new AiProviderFailed('A IA não gerou um artigo utilizável. Tente novamente.', 'empty_blog_content');
            }
            $this->log($settings, true, null, $result->inputTokens, $result->outputTokens, $result->totalTokens, $result->durationMs);

            return $fields;
        } catch (AiProviderFailed $exception) {
            $this->log($settings, false, $exception->errorCode);
            throw $exception;
        } catch (Throwable $exception) {
            $this->log($settings, false, 'unexpected_error');
            report($exception);
            throw new AiProviderFailed('Não foi possível gerar o artigo com IA. Tente novamente.', 'unexpected_error', previous: $exception);
        }
    }

    private function limited(mixed $text, int $limit): ?string
    {
        return is_string($text) && trim($text) !== '' ? mb_substr(trim($text), 0, $limit) : null;
    }

    private function log(AiSetting $settings, bool $success, ?string $error, int $input = 0, int $output = 0, int $total = 0, ?int $duration = null): void
    {
        AiUsageLog::query()->create([
            'provider' => $settings->provider,
            'model' => $settings->model,
            'action' => 'blog_content',
            'product_id' => null,
            'user_id' => Auth::guard('web')->id(),
            'input_tokens' => max(0, $input),
            'output_tokens' => max(0, $output),
            'total_tokens' => max(0, $total),
            'duration_ms' => $duration,
            'successful' => $success,
            'error_code' => $error,
        ]);
    }
}
