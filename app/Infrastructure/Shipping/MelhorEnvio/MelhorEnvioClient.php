<?php

declare(strict_types=1);

namespace App\Infrastructure\Shipping\MelhorEnvio;

use App\Domain\Shipping\Data\ApiMoney;
use App\Domain\Shipping\Exceptions\ShippingProviderFailed;
use App\Domain\Shipping\Models\ShippingIntegration;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class MelhorEnvioClient
{
    public function integration(): ShippingIntegration
    {
        return ShippingIntegration::forProvider('melhor_envio');
    }

    public function baseUrl(?ShippingIntegration $integration = null): string
    {
        return ($integration ?? $this->integration())->environment === 'production' ? 'https://melhorenvio.com.br' : 'https://sandbox.melhorenvio.com.br';
    }

    public function callbackUrl(): string
    {
        return trim((string) config('shipping.melhor_envio.callback_url')) ?: route('admin.shipping.melhor-envio.callback');
    }

    public function authorizeUrl(ShippingIntegration $integration, string $redirectUri, string $state): string
    {
        $id = trim((string) ($integration->credentials['client_id'] ?? ''));
        if ($id === '') {
            throw new ShippingProviderFailed('Informe o Client ID do Melhor Envio antes de conectar.');
        }

        return $this->baseUrl($integration).'/oauth/authorize?'.http_build_query([
            'client_id' => $id, 'redirect_uri' => $redirectUri, 'response_type' => 'code', 'state' => $state,
            'scope' => implode(' ', config('shipping.melhor_envio.scopes', [])),
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function exchangeAuthorizationCode(ShippingIntegration $integration, string $code, string $redirectUri): array
    {
        return $this->tokenRequest($integration, ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => $redirectUri]);
    }

    public function get(string $path, array $query = []): array
    {
        return $this->json($this->send('get', $path, $query));
    }

    public function post(string $path, array $payload): array
    {
        $data = $this->json($this->send('post', $path, $payload));
        if ($path !== '/api/v2/me/shipment/calculate') {
            $failed = filled($data['error'] ?? null) || filled($data['errors'] ?? null) || ($data['success'] ?? true) === false;
            foreach ($data as $row) {
                if (is_array($row) && (($row['status'] ?? true) === false || filled($row['error'] ?? null))) {
                    $failed = true;
                }
            }
            if ($failed) {
                $message = $this->responseMessage($data);
                throw new ShippingProviderFailed(
                    $message ? 'O Melhor Envio não confirmou a operação: '.$message : 'O Melhor Envio não confirmou a operação. Concilie o envio antes de continuar.',
                    uncertain: true,
                );
            }
        }

        return $data;
    }

    public function download(string $path): Response
    {
        return $this->send('get', $path);
    }

    private function send(string $method, string $path, array $payload = [], bool $retried = false): Response
    {
        $integration = $this->integration();
        if (! $integration->access_token) {
            throw new ShippingProviderFailed('Conecte a conta do Melhor Envio antes de utilizar a integração.');
        }
        if (! $retried && $integration->token_expires_at?->isPast() && $integration->refresh_token) {
            $this->refreshToken($integration);
            $integration->refresh();
        }
        try {
            $request = $this->request($integration)->withToken((string) $integration->access_token);
            $response = $method === 'get' ? $request->get($this->baseUrl($integration).$path, $payload)
                : $request->withBody(ApiMoney::json($payload), 'application/json')->post($this->baseUrl($integration).$path);
        } catch (Throwable) {
            Log::warning('Shipping request unavailable', ['provider' => 'melhor_envio', 'endpoint' => $path]);
            throw new ShippingProviderFailed('O Melhor Envio está temporariamente indisponível. Verifique a conexão e tente novamente.', uncertain: $method === 'post');
        }
        if ($response->status() === 401 && ! $retried && $integration->refresh_token) {
            $this->refreshToken($integration);

            return $this->send($method, $path, $payload, true);
        }
        if (! $response->successful()) {
            $providerMessage = $this->responseMessage($response->json());
            Log::warning('Shipping request rejected', array_filter([
                'provider' => 'melhor_envio',
                'endpoint' => $path,
                'status' => $response->status(),
                'request_id' => $response->header('X-Request-Id'),
                'provider_message' => $providerMessage,
            ], static fn (mixed $value): bool => $value !== null && $value !== ''));

            throw new ShippingProviderFailed(
                $this->friendlyFailureMessage($response->status(), $providerMessage),
                uncertain: $method === 'post' && $response->serverError(),
            );
        }

        return $response;
    }

    private function json(Response $response): array
    {
        $data = $response->json();
        if (! is_array($data)) {
            throw new ShippingProviderFailed('O Melhor Envio retornou uma resposta inválida.', uncertain: true);
        }

        return $data;
    }

    private function request(ShippingIntegration $integration): PendingRequest
    {
        $config = $integration->configuration ?? [];
        $name = trim((string) ($config['application_name'] ?? (config('app.name', 'Saba Equipamentos').' E-commerce'))) ?: 'SABA E-commerce';
        $email = trim((string) ($config['technical_email'] ?? config('mail.from.address'))) ?: 'suporte@saba.local';

        return Http::acceptJson()->withHeaders(['User-Agent' => $name.' ('.$email.')'])->connectTimeout(5)->timeout(15);
    }

    private function tokenRequest(ShippingIntegration $integration, array $payload): array
    {
        try {
            $response = $this->request($integration)->asForm()->post($this->baseUrl($integration).'/oauth/token', array_merge($payload, [
                'client_id' => $integration->credentials['client_id'] ?? null,
                'client_secret' => $integration->credentials['client_secret'] ?? null,
            ]));
        } catch (Throwable) {
            throw new ShippingProviderFailed('Não foi possível obter a autorização do Melhor Envio. Tente novamente.');
        }
        if (! $response->successful()) {
            Log::warning('Shipping OAuth rejected', ['provider' => 'melhor_envio', 'endpoint' => '/oauth/token', 'status' => $response->status()]);
            throw new ShippingProviderFailed('Não foi possível autorizar o Melhor Envio. Confira ambiente, Client ID, Secret e callback.');
        }

        return $this->json($response);
    }

    private function refreshToken(ShippingIntegration $integration): void
    {
        $oldToken = $integration->access_token;
        Cache::lock('shipping-token:'.$integration->id, 30)->block(5, function () use ($integration, $oldToken): void {
            $integration->refresh();
            if ($integration->access_token !== $oldToken) {
                return;
            }
            $tokens = $this->tokenRequest($integration, ['grant_type' => 'refresh_token', 'refresh_token' => $integration->refresh_token]);
            $this->storeTokens($integration, $tokens);
        });
    }

    public function storeTokens(ShippingIntegration $integration, array $tokens): void
    {
        if (! is_string($tokens['access_token'] ?? null) || trim($tokens['access_token']) === '') {
            throw new ShippingProviderFailed('O Melhor Envio não retornou uma autorização válida.');
        }
        $integration->forceFill([
            'access_token' => $tokens['access_token'], 'refresh_token' => $tokens['refresh_token'] ?? $integration->refresh_token,
            'token_expires_at' => now()->addSeconds(max(60, (int) ($tokens['expires_in'] ?? 2592000))),
            'connected_at' => $integration->connected_at ?? now(), 'last_tested_at' => now(),
        ])->save();
        Cache::forever('shipping-configuration-revision', (string) Str::uuid());
    }

    private function friendlyFailureMessage(int $status, ?string $providerMessage): string
    {
        if ($status === 401) {
            return 'A autorização do Melhor Envio expirou ou foi recusada. Reconecte a conta e tente novamente.';
        }
        if ($status === 403) {
            return 'O aplicativo do Melhor Envio não possui permissão para esta operação. Revise os escopos e reconecte a conta.';
        }

        if ($status === 422) {
            $normalized = mb_strtolower((string) $providerMessage);
            if (str_contains($normalized, 'nota fiscal') || str_contains($normalized, 'invoice') || str_contains($normalized, 'nfe') || str_contains($normalized, 'nf-e')) {
                return 'O Melhor Envio recusou a NF-e. Use uma chave real de NF-e modelo 55; no Sandbox as chaves também são validadas.'.($providerMessage ? ' Detalhe: '.$providerMessage : '');
            }
            if (str_contains($normalized, 'agenc') || str_contains($normalized, 'unidade')) {
                return 'O Melhor Envio recusou a unidade de postagem selecionada. Atualize as unidades da transportadora e tente novamente.'.($providerMessage ? ' Detalhe: '.$providerMessage : '');
            }
            if (str_contains($normalized, 'dce') || str_contains($normalized, 'declara')) {
                return 'O Melhor Envio recusou os dados da declaração de conteúdo/DC-e. Confira os itens declarados e os dados fiscais do remetente.'.($providerMessage ? ' Detalhe: '.$providerMessage : '');
            }
            if (str_contains($normalized, 'product') || str_contains($normalized, 'produto') || str_contains($normalized, 'unitary_value')) {
                return 'O Melhor Envio recusou algum item da declaração do envio. Confira produtos, quantidades e valores.'.($providerMessage ? ' Detalhe: '.$providerMessage : '');
            }
            if (str_contains($normalized, 'inscri') || str_contains($normalized, 'state_register')) {
                return 'O Melhor Envio recusou a Inscrição Estadual do remetente. Revise os dados fiscais em Configurações da loja e o tipo de documento escolhido para o envio.'.($providerMessage ? ' Detalhe: '.$providerMessage : '');
            }
            if (str_contains($normalized, 'cep') || str_contains($normalized, 'postal')) {
                return 'O Melhor Envio recusou algum dado de endereço ou CEP do envio.'.($providerMessage ? ' Detalhe: '.$providerMessage : '');
            }

            return $providerMessage
                ? 'O Melhor Envio recusou os dados do envio: '.$providerMessage
                : 'O Melhor Envio recusou os dados do envio. Revise documento fiscal, remetente, destinatário, produtos, volumes e serviço.';
        }

        if ($status === 402 || str_contains(mb_strtolower((string) $providerMessage), 'saldo')) {
            return 'Não há saldo suficiente na carteira do Melhor Envio para comprar esta etiqueta.';
        }

        if ($responseMessage = $providerMessage) {
            return 'Não foi possível concluir a operação no Melhor Envio: '.$responseMessage;
        }

        return 'Não foi possível concluir a operação no Melhor Envio. Verifique a conexão e os dados do envio.';
    }

    private function responseMessage(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        $messages = [];
        $collect = function (mixed $value, ?string $key = null, bool $errorContext = false) use (&$collect, &$messages): void {
            if (count($messages) >= 4) {
                return;
            }

            $normalizedKey = mb_strtolower((string) $key);
            $errorContext = $errorContext || in_array($normalizedKey, ['errors', 'error', 'message', 'detail', 'description', 'error_description'], true)
                || str_contains($normalizedKey, 'error') || str_contains($normalizedKey, 'message');

            if (is_string($value)) {
                if (! $errorContext) {
                    return;
                }
                $clean = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
                if ($clean !== '' && ! in_array($clean, $messages, true)) {
                    $messages[] = mb_substr($clean, 0, 350);
                }

                return;
            }
            if (! is_array($value)) {
                return;
            }
            foreach ($value as $childKey => $child) {
                if (is_string($childKey)) {
                    $childKeyLower = mb_strtolower($childKey);
                    if (str_contains($childKeyLower, 'token') || str_contains($childKeyLower, 'secret') || str_contains($childKeyLower, 'authorization')) {
                        continue;
                    }
                }
                $collect($child, is_string($childKey) ? $childKey : $key, $errorContext);
            }
        };
        $collect($payload);

        return $messages === [] ? null : implode(' · ', array_unique($messages));
    }
}
