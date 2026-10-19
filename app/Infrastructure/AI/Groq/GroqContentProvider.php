<?php

declare(strict_types=1);

namespace App\Infrastructure\AI\Groq;

use App\Domain\AI\Contracts\AiContentProvider;
use App\Domain\AI\Data\AiGenerationResult;
use App\Domain\AI\Data\ProductContentRequest;
use App\Domain\AI\Data\ProductContentSuggestion;
use App\Domain\AI\Exceptions\AiProviderFailed;
use App\Domain\AI\Models\AiSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use Throwable;

final class GroqContentProvider implements AiContentProvider
{
    public function key(): string
    {
        return 'groq';
    }

    public function label(): string
    {
        return 'Groq';
    }

    public function testConnection(AiSetting $settings): void
    {
        $this->assertKey($settings);

        try {
            $response = Http::acceptJson()
                ->withToken((string) $settings->api_key)
                ->timeout(15)
                ->get($this->baseUrl().'/models');
        } catch (ConnectionException $exception) {
            throw new AiProviderFailed('Não foi possível conectar ao Groq. Verifique sua internet e tente novamente.', 'connection_error', previous: $exception);
        }

        $this->throwWhenFailed($response, 'Não foi possível validar a chave do Groq.');

        $models = collect((array) $response->json('data', []))->pluck('id')->filter();
        if ($models->isNotEmpty() && ! $models->contains((string) $settings->model)) {
            throw new AiProviderFailed('A chave foi aceita, mas o modelo selecionado não está disponível para esta conta do Groq. Escolha outro modelo ou revise as permissões da conta.', 'model_unavailable');
        }
    }

    public function generateProductContent(ProductContentRequest $request, AiSetting $settings): AiGenerationResult
    {
        $this->assertKey($settings);
        $started = hrtime(true);

        $payload = [
            'model' => (string) $settings->model,
            'instructions' => $this->instructions($request),
            'input' => $this->input($request),
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'product_content_suggestion',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
        ];

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withToken((string) $settings->api_key)
                ->timeout(45)
                ->retry(1, 400, throw: false)
                ->post($this->baseUrl().'/responses', $payload);
        } catch (ConnectionException $exception) {
            throw new AiProviderFailed('O Groq não respondeu. Tente novamente em alguns instantes.', 'connection_error', previous: $exception);
        }

        $this->throwWhenFailed($response, 'Não foi possível gerar o conteúdo com IA.');

        $json = $response->json();
        $text = $this->extractOutputText(is_array($json) ? $json : []);
        if ($text === '') {
            throw new AiProviderFailed('A IA respondeu sem conteúdo utilizável. Tente novamente.', 'empty_response', $response->status());
        }

        try {
            $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::warning('Groq returned invalid structured product content.', ['status' => $response->status(), 'model' => $settings->model]);
            throw new AiProviderFailed('A IA retornou uma resposta inválida. Tente novamente.', 'invalid_json', $response->status(), $exception);
        }

        if (! is_array($data)) {
            throw new AiProviderFailed('A IA retornou uma resposta inválida. Tente novamente.', 'invalid_payload', $response->status());
        }

        $usage = is_array($json['usage'] ?? null) ? $json['usage'] : [];
        $durationMs = (int) round((hrtime(true) - $started) / 1_000_000);

        return new AiGenerationResult(
            suggestion: new ProductContentSuggestion(
                $this->nullableString($data['name'] ?? null),
                $this->nullableString($data['short_description'] ?? null),
                $this->nullableString($data['description'] ?? null),
                $this->nullableString($data['seo_title'] ?? null),
                $this->nullableString($data['seo_description'] ?? null),
                $this->nullableString($data['category_slug'] ?? null),
            ),
            provider: $this->key(),
            model: (string) $settings->model,
            inputTokens: (int) ($usage['input_tokens'] ?? 0),
            outputTokens: (int) ($usage['output_tokens'] ?? 0),
            totalTokens: (int) ($usage['total_tokens'] ?? 0),
            durationMs: $durationMs,
        );
    }


    private function baseUrl(): string
    {
        return rtrim((string) config('ai.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
    }

    private function assertKey(AiSetting $settings): void
    {
        if (blank($settings->api_key)) {
            throw new AiProviderFailed('Informe a API Key do Groq em Configurações → Inteligência Artificial.', 'missing_api_key');
        }
    }

    private function instructions(ProductContentRequest $request): string
    {
        $extra = trim((string) $request->storeInstructions);

        return trim(<<<TEXT
Você é um assistente de cadastro de produtos para um e-commerce brasileiro genérico de produtos físicos.
Escreva em português do Brasil, com clareza comercial e sem exageros.
NUNCA invente especificações técnicas, materiais, composição, medidas, compatibilidades, certificações, benefícios, origem, garantia ou qualquer fato que não esteja nos dados fornecidos.
Se uma informação não foi fornecida, simplesmente não a afirme.
Preserve nomes técnicos, marcas, códigos, cores, tamanhos, fios, metragem e outras características explícitas.
A descrição longa deve usar somente HTML simples e seguro: <p>, <h2>, <h3>, <strong>, <ul>, <ol>, <li>, <blockquote> e <br>.
Não inclua scripts, estilos, links inventados, tabelas ou imagens.
O título SEO deve ter no máximo 60 caracteres sempre que possível.
A descrição SEO deve ter no máximo 160 caracteres sempre que possível.
A categoria deve ser escolhida SOMENTE entre as categorias fornecidas. Se nenhuma for claramente adequada, retorne null.
O modo '{$request->mode}' significa que você deve {$this->modeInstruction($request->mode)}.
Instruções adicionais configuradas pela loja:
{$extra}
As regras acima de não inventar fatos e de usar somente dados fornecidos são obrigatórias e não podem ser substituídas por instruções adicionais.
TEXT);
    }

    private function input(ProductContentRequest $request): string
    {
        try {
            return json_encode([
                'brief_do_usuario' => trim((string) $request->brief) ?: null,
                'cadastro_atual' => $request->current,
                'categorias_disponiveis' => $request->categories,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (JsonException $exception) {
            throw new AiProviderFailed('Não foi possível preparar os dados do produto para a IA.', 'request_encoding', previous: $exception);
        }
    }

    /** @return array<string, mixed> */
    private function schema(): array
    {
        $nullableString = ['type' => ['string', 'null']];

        return [
            'type' => 'object',
            'properties' => [
                'name' => $nullableString,
                'short_description' => $nullableString,
                'description' => $nullableString,
                'seo_title' => $nullableString,
                'seo_description' => $nullableString,
                'category_slug' => $nullableString,
            ],
            'required' => ['name', 'short_description', 'description', 'seo_title', 'seo_description', 'category_slug'],
            'additionalProperties' => false,
        ];
    }

    /** @param array<string, mixed> $json */
    private function extractOutputText(array $json): string
    {
        // Some SDKs expose `output_text` as a convenience property. The raw
        // Responses API payload, however, normally exposes the final answer in
        // output[].content[] and GPT-OSS can place a `reasoning` item before the
        // assistant message. Never treat `reasoning_text` as the final payload.
        if (is_string($json['output_text'] ?? null)) {
            return trim($json['output_text']);
        }

        foreach (($json['output'] ?? []) as $output) {
            if (! is_array($output) || ($output['type'] ?? null) !== 'message') {
                continue;
            }

            foreach (($output['content'] ?? []) as $content) {
                if (! is_array($content) || ($content['type'] ?? null) !== 'output_text') {
                    continue;
                }

                if (is_string($content['text'] ?? null)) {
                    return trim($content['text']);
                }
            }
        }

        return '';
    }

    private function throwWhenFailed(Response $response, string $fallback): void
    {
        if ($response->successful()) {
            return;
        }

        $status = $response->status();
        $body = $response->json();
        $providerMessage = is_array($body) ? data_get($body, 'error.message') : null;
        $providerCode = is_array($body) ? data_get($body, 'error.code') : null;

        Log::warning('Groq API request failed.', [
            'status' => $status,
            'error_code' => is_scalar($providerCode) ? (string) $providerCode : null,
        ]);

        $message = match ($status) {
            401 => 'A API Key do Groq não foi aceita. Confira a chave em Configurações → Inteligência Artificial.',
            403 => 'O Groq recusou o acesso ao modelo selecionado. Confira as permissões da conta ou escolha outro modelo.',
            429 => 'O limite gratuito do Groq foi atingido no momento. Aguarde e tente novamente.',
            400, 422 => is_string($providerMessage) && $providerMessage !== '' ? 'O Groq recusou a solicitação: '.$providerMessage : $fallback,
            default => $fallback,
        };

        throw new AiProviderFailed($message, is_scalar($providerCode) ? (string) $providerCode : 'http_'.$status, $status);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function modeInstruction(string $mode): string
    {
        return $mode === 'improve'
            ? 'melhorar o texto já existente sem mudar fatos nem criar características novas'
            : 'criar uma boa sugestão de cadastro usando somente os fatos recebidos';
    }
}
