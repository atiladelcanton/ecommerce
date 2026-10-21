<?php

declare(strict_types=1);

namespace App\Infrastructure\Payments;

use App\Domain\Payment\Contracts\InspectablePaymentGateway;
use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Data\PaymentResult;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntegration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class MercadoPagoGateway implements InspectablePaymentGateway
{
    public function __construct(private readonly PaymentIntegration $integration) {}

    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function cents(string $amount): ?int
    {
        if (! preg_match('/^([0-9]+)\.([0-9]{2})$/', $amount, $matches)) {
            return null;
        }
        $whole = (int) $matches[1];
        if ($whole > intdiv(PHP_INT_MAX - 99, 100)) {
            return null;
        }
        return $whole * 100 + (int) $matches[2];
    }

    public function createPayment(PaymentRequest $request): PaymentResult
    {
        if ($request->amount <= 0 || $request->buyerEmail === null || $request->returnUrl === null || $request->expirationHours < 1) {
            throw ValidationException::withMessages(['payment' => 'Dados insuficientes para iniciar a cobrança.']);
        }
        $items = $request->items ?: [['title' => 'Pedido '.$request->orderNumber, 'quantity' => 1, 'unit_price' => self::decimal($request->amount)]];
        $sum = 0;
        foreach ($items as $item) {
            $value = self::cents($item['unit_price']);
            if ($value === null || $value <= 0 || $item['quantity'] <= 0) {
                throw ValidationException::withMessages(['payment' => 'Itens inválidos para o Mercado Pago.']);
            }
            $sum += $value * $item['quantity'];
        }
        if ($sum !== $request->amount) {
            throw ValidationException::withMessages(['payment' => 'Os itens não correspondem ao total da cobrança.']);
        }
        // The Orders API accepts ISO-8601 durations (P3D instead of PT72H).
        $expiration = $request->expirationHours % 24 === 0
            ? 'P'.intdiv($request->expirationHours, 24).'D'
            : 'PT'.$request->expirationHours.'H';
        $online = ['success_url' => $request->returnUrl, 'failure_url' => $request->returnUrl, 'pending_url' => $request->returnUrl, 'auto_return' => 'approved'];
        // Checkout Pro Orders API accepts title, quantity and unit_price per item.
        // total_amount belongs ONLY at the order root; unit_measure is unsupported here.
        $items = array_map(static function (array $item): array {
            return [
                'title' => $item['title'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ];
        }, $items);
        $payload = [
            'type' => 'online', 'processing_mode' => 'manual', 'capture_mode' => 'automatic_async',
            'total_amount' => self::decimal($request->amount), 'external_reference' => $request->orderNumber,
            'expiration_time' => $expiration,
            'payer' => ['email' => $request->buyerEmail], 'items' => $items,
            'config' => ['online' => $online],
        ];
        if ($request->allowOffline) {
            $payload['config']['default_payment_due_date'] = $expiration;
        } else {
            $payload['config']['payment_method'] = ['not_allowed_types' => ['ticket']];
        }
        try {
            $response = $this->request()->withHeaders(['X-Idempotency-Key' => $request->idempotencyKey])->post('/v1/orders', $payload);
        } catch (ConnectionException $exception) {
            Log::warning('Payment provider connection failure during checkout initialization.', [
                'provider' => 'mercado_pago',
                'environment' => $this->integration->environment,
                'integration_id' => $this->integration->id,
                'operation' => 'create_order',
                'order_number' => $request->orderNumber,
                'failure' => 'connection',
            ]);
            // The operation's outcome is unknown. Do not release reserved stock.
            throw new \RuntimeException('A inicialização do pagamento não pôde ser confirmada.', previous: $exception);
        }
        if (! $response->successful()) {
            $this->logFailure($request, $response);
            if ($response->serverError() || in_array($response->status(), [408, 409, 423, 425, 429], true)) {
                // An uncertain result must retain its original idempotency key and stock hold.
                throw new \RuntimeException('A inicialização do pagamento não pôde ser confirmada.');
            }
            throw ValidationException::withMessages([
                'payment' => 'Não foi possível iniciar seu pagamento. Tente novamente ou entre em contato com a loja.',
            ]);
        }
        $id = $response->json('id');
        $url = $response->json('checkout_url');
        if (! is_string($id) || ! preg_match('/^ORD[A-Za-z0-9_-]+$/', $id) || ! is_string($url) || ! $this->isCheckoutUrl($url)) {
            Log::warning('Payment provider returned unexpected checkout metadata.', [
                'provider' => 'mercado_pago',
                'environment' => $this->integration->environment,
                'integration_id' => $this->integration->id,
                'operation' => 'create_order',
                'order_number' => $request->orderNumber,
                'http_status' => $response->status(),
                'has_order_id' => is_string($id),
                'has_valid_checkout_url' => is_string($url) && $this->isCheckoutUrl($url),
            ]);
            throw new \RuntimeException('A resposta do pagamento não pôde ser confirmada.');
        }
        return new PaymentResult('mercado_pago', $id, PaymentStatus::PENDING, false, $url, (string) $response->json('status', 'created'), (string) $response->json('status_detail', 'created'));
    }

    public function inspect(string $externalId): array
    {
        if (! preg_match('/^ORD[A-Za-z0-9_-]+$/', $externalId)) {
            throw ValidationException::withMessages(['payment' => 'Identificação da cobrança inválida.']);
        }
        $response = $this->request()->get('/v1/orders/'.rawurlencode($externalId));
        if (! $response->successful()) {
            throw new \RuntimeException('Falha temporária ao consultar o Mercado Pago: HTTP '.$response->status());
        }
        $json = $response->json();
        if (! is_array($json) || ($json['id'] ?? null) !== $externalId) {
            throw new \RuntimeException('O Mercado Pago retornou identificação inconsistente.');
        }
        return $json;
    }

    public function testConnection(): bool
    {
        return $this->request()->get('/v1/payment_methods')->successful();
    }

    /**
     * Record bounded and sanitized provider diagnostics, never the request payload,
     * authorization token, payer identity, full response or checkout URLs.
     */
    private function logFailure(PaymentRequest $request, \Illuminate\Http\Client\Response $response): void
    {
        $body = $response->json();
        $details = [];
        if (is_array($body)) {
            foreach (['errors', 'cause', 'details'] as $key) {
                foreach (array_slice(is_array($body[$key] ?? null) ? $body[$key] : [], 0, 4) as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }
                    $details[] = array_filter([
                        'code' => self::safeCode($entry['code'] ?? $entry['error'] ?? null),
                        'field' => self::safeCode($entry['field'] ?? $entry['property'] ?? null),
                        'message' => self::safeMessage($entry['message'] ?? $entry['description'] ?? null),
                    ], static fn ($value) => $value !== null);
                }
            }
        }
        Log::warning('Payment provider rejected checkout initialization.', [
            'provider' => 'mercado_pago',
            'environment' => $this->integration->environment,
            'integration_id' => $this->integration->id,
            'operation' => 'create_order',
            'order_number' => $request->orderNumber,
            'http_status' => $response->status(),
            'request_id' => self::safeCode($response->header('x-request-id')),
            'error_code' => self::safeCode(is_array($body) ? ($body['error'] ?? $body['code'] ?? null) : null),
            'error_message' => self::safeMessage(is_array($body) ? ($body['message'] ?? null) : null),
            'details' => $details,
        ]);
    }

    private static function safeCode(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }
        $value = (string) $value;
        return preg_match('/^[a-zA-Z0-9_.\[\]:\/-]{1,100}$/D', $value) ? $value : null;
    }

    private static function safeMessage(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        // Provider messages may echo submitted values. Mask URLs, emails, tokens,
        // long identifiers and document numbers before sending them to the log.
        $value = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value);
        $value = preg_replace('~https?://[^\s\"\'<>]+~iu', '[URL]', $value);
        $value = preg_replace('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/iu', '[EMAIL]', $value);
        $value = preg_replace('/\b(?:Bearer\s+|APP_USR-|TEST-)[a-z0-9_.\-]+/iu', '[REDACTED]', $value);
        $value = preg_replace('/\b[a-z0-9_\-]{32,}\b/iu', '[IDENTIFIER]', $value);
        $value = preg_replace('/\b\d{11,}\b/u', '[NUMBER]', $value);
        return mb_substr(trim($value), 0, 200);
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        if ($this->integration->accessToken() === '') {
            throw ValidationException::withMessages(['payment' => 'Configure o Access Token do Mercado Pago.']);
        }
        return Http::baseUrl('https://api.mercadopago.com')->acceptJson()->asJson()
            ->withToken($this->integration->accessToken())->connectTimeout(5)->timeout(15);
    }

    public static function isCheckoutUrl(string $url): bool
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        return ($parts['scheme'] ?? '') === 'https'
            && ($host === 'mercadopago.com.br' || str_ends_with($host, '.mercadopago.com.br'))
            && ! isset($parts['user']) && ! isset($parts['pass']);
    }
}
