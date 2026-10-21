<?php

declare(strict_types=1);

namespace App\Infrastructure\Payments;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Data\PaymentRequest;
use App\Domain\Payment\Data\PaymentResult;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntegration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Hosted Asaas Checkout: only PIX and CREDIT_CARD, no card data in SABA. */
final class AsaasGateway implements PaymentGateway
{
    public function __construct(private readonly PaymentIntegration $integration) {}

    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    /** Parse external money without converting financial arithmetic to floats. */
    public static function cents(string $value): ?int
    {
        if (! preg_match('/^(0|[1-9][0-9]*)(?:\.([0-9]{1,2}))?$/', $value, $matches)) {
            return null;
        }
        $whole = (int) $matches[1];
        if ($whole > intdiv(PHP_INT_MAX - 99, 100)) {
            return null;
        }
        return ($whole * 100) + (int) str_pad($matches[2] ?? '', 2, '0');
    }

    /**
     * Accept only Asaas-owned HTTPS checkout links and the matching environment.
     * The provider changes public URL path formats; never reconstruct or constrain
     * its path to a specific pattern. In particular, Sandbox and production
     * checkout domains must not be mixed.
     */
    public static function isCheckoutUrl(?string $url, ?string $environment = null): bool
    {
        if (! is_string($url) || strlen($url) > 2048) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts) || ($parts['scheme'] ?? null) !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])
            || isset($parts['fragment']) || empty($parts['path']) || $parts['path'] === '/') {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        if ($environment === 'sandbox') {
            return $host === 'sandbox.asaas.com';
        }
        if ($environment === 'production') {
            return in_array($host, ['asaas.com', 'www.asaas.com'], true);
        }

        return in_array($host, ['asaas.com', 'www.asaas.com', 'sandbox.asaas.com'], true);
    }

    public function createPayment(PaymentRequest $request): PaymentResult
    {
        $minutes = $request->expirationMinutes ?? 60;
        if ($request->amount <= 0 || $request->returnUrl === null || (! str_starts_with($request->returnUrl, 'https://') && ! app()->environment('testing'))
            || $minutes < 10 || $minutes > 1440) {
            throw ValidationException::withMessages(['payment' => 'Não foi possível iniciar seu pagamento. Confira os dados do pedido.']);
        }
        $config = $this->integration->configuration ?? [];
        $billing = array_values(array_filter([
            ($config['pix'] ?? true) ? 'PIX' : null,
            ($config['credit_card'] ?? true) ? 'CREDIT_CARD' : null,
        ]));
        if ($billing === []) {
            throw ValidationException::withMessages(['payment' => 'Nenhuma modalidade de pagamento foi habilitada.']);
        }
        $maxInstallments = (int) ($config['max_installments'] ?? 1);
        $installments = in_array('CREDIT_CARD', $billing, true) && $maxInstallments > 1;
        $rows = $request->items ?: [[
            'title' => 'Pedido '.$request->orderNumber, 'quantity' => 1, 'unit_price' => self::decimal($request->amount),
        ]];
        $total = 0;
        $items = [];
        $replacements = [];
        foreach ($rows as $index => $row) {
            $price = self::cents((string) ($row['unit_price'] ?? ''));
            $qty = $row['quantity'] ?? null;
            if ($price === null || $price <= 0 || ! is_int($qty) || $qty <= 0 || $qty > 999) {
                throw ValidationException::withMessages(['payment' => 'Os itens do pedido precisam ser conferidos.']);
            }
            $total += $price * $qty;
            $placeholder = '__SABA_ASAAS_MONEY_'.$index.'__';
            $replacements['"'.$placeholder.'"'] = self::decimal($price);
            $items[] = ['name' => mb_substr((string) ($row['title'] ?? 'Produto'), 0, 120), 'quantity' => $qty, 'value' => $placeholder];
        }
        if ($total !== $request->amount) {
            throw ValidationException::withMessages(['payment' => 'O valor dos itens diverge do total do pedido.']);
        }
        $payload = [
            'billingTypes' => $billing,
            'chargeTypes' => $installments ? ['DETACHED', 'INSTALLMENT'] : ['DETACHED'],
            'minutesToExpire' => $minutes,
            'externalReference' => $request->orderNumber,
            'callback' => [
                'successUrl' => $request->returnUrl,
                'cancelUrl' => $request->returnUrl,
                'expiredUrl' => $request->returnUrl,
            ],
            'items' => $items,
        ];
        if ($installments) {
            $payload['installment'] = ['maxInstallmentCount' => $maxInstallments];
        }
        if ($request->buyerName && $request->buyerDocument && $request->buyerEmail) {
            // Checkout Asaas requires a complete address when customerData is sent.
            // Read only the immutable address snapshot of the order; never trust
            // client-supplied address values while creating the remote checkout.
            $address = $request->buyerAddress;
            $street = trim((string) ($address['street'] ?? ''));
            $number = trim((string) ($address['number'] ?? ''));
            $postalCode = preg_replace('/\D/', '', (string) ($address['postal_code'] ?? ''));
            $neighborhood = trim((string) ($address['neighborhood'] ?? ''));
            if ($street === '' || $number === '' || ! preg_match('/^[0-9]{8}$/', $postalCode) || $neighborhood === '') {
                throw ValidationException::withMessages([
                    'payment' => 'O endereço do pedido está incompleto. Confira os dados de entrega para continuar.',
                ]);
            }
            $payload['customerData'] = [
                'name' => $request->buyerName,
                'cpfCnpj' => $request->buyerDocument,
                'email' => $request->buyerEmail,
                'address' => $street,
                'addressNumber' => $number,
                'postalCode' => $postalCode,
                'province' => $neighborhood,
            ];
            if ($request->buyerPhone !== null && trim($request->buyerPhone) !== '') {
                $payload['customerData']['phone'] = trim($request->buyerPhone);
            }
            $complement = trim((string) ($address['complement'] ?? ''));
            if ($complement !== '') {
                $payload['customerData']['complement'] = $complement;
            }
            // Asaas customerData.city accepts an IBGE code, not the city name
            // stored by SABA in order_addresses. Omit it rather than fabricate an ID.
        }
        // Asaas expects JSON numbers, not strings. Replace only known sentinel values
        // AFTER serializing the payload: exact decimal output, no PHP float arithmetic.
        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $json = str_replace(array_keys($replacements), array_values($replacements), $json);
        try {
            $response = $this->client()->withBody($json, 'application/json')->post('/checkouts');
        } catch (ConnectionException $exception) {
            Log::warning('Asaas checkout initialization uncertain.', ['payment_reference' => $request->orderNumber, 'integration_id' => $this->integration->id]);
            // There is no documented create-checkout idempotency guarantee.
            // NEVER auto-repeat this POST: retain hold and require inspection.
            throw new \RuntimeException('Resultado inconclusivo ao criar checkout Asaas.', previous: $exception);
        }
        if (! $response->successful()) {
            // Keep the diagnostics useful without persisting response bodies or
            // personal information potentially embedded in validation messages.
            $errorCodes = [];
            $errorFields = [];
            $responseErrors = $response->json('errors');
            if (is_array($responseErrors)) {
                foreach (array_slice($responseErrors, 0, 10) as $error) {
                    if (! is_array($error)) {
                        continue;
                    }
                    $code = $error['code'] ?? null;
                    if (is_string($code) && preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $code)) {
                        $errorCodes[] = $code;
                    }
                    $description = $error['description'] ?? null;
                    if (is_string($description) && preg_match('/\bcampo\s+([a-zA-Z][a-zA-Z0-9_]*)\b/ui', $description, $matches)) {
                        $errorFields[] = $matches[1];
                    }
                }
            }
            Log::warning('Asaas rejected checkout initialization.', [
                'order_number' => $request->orderNumber, 'integration_id' => $this->integration->id,
                'http_status' => $response->status(), 'request_id' => mb_substr((string) $response->header('x-request-id', ''), 0, 100),
                'error_codes' => array_values(array_unique($errorCodes)),
                'validation_fields' => array_values(array_unique($errorFields)),
            ]);
            if ($response->serverError() || in_array($response->status(), [408, 409, 425, 429], true)) {
                throw new \RuntimeException('Resultado inconclusivo ao criar checkout Asaas.');
            }
            throw ValidationException::withMessages(['payment' => 'Não foi possível iniciar seu pagamento. Tente novamente ou entre em contato com a loja.']);
        }
        $id = $response->json('id');
        if (! is_string($id) || ! preg_match('/^[a-zA-Z0-9_-]{12,100}$/', $id)) {
            throw new \RuntimeException('Resposta inconclusiva do Asaas: ID de checkout inválido.');
        }
        // Asaas has changed its public checkout URLs. The API returns the
        // definitive URL, including the Sandbox host and optional path prefix.
        // Trust only an Asaas-owned HTTPS URL for this exact integration environment.
        // Do not synthesize an old /checkoutSession/show?id=... address.
        $url = $response->json('link');
        if (! self::isCheckoutUrl($url, $this->integration->environment)) {
            Log::warning('Asaas returned invalid or missing checkout link.', [
                'order_number' => $request->orderNumber,
                'integration_id' => $this->integration->id,
                'environment' => $this->integration->environment,
                'has_link' => is_string($url),
            ]);
            // The remote checkout may already exist: do not retry its POST.
            throw new \RuntimeException('Resposta inconclusiva do Asaas: URL de checkout inválida.');
        }

        return new PaymentResult('asaas', $id, PaymentStatus::PENDING, false, $url, 'ACTIVE', 'checkout_created');
    }

    public function testConnection(): bool
    {
        return $this->client()->get('/myAccount/status/')->successful();
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $base = $this->integration->environment === 'production'
            ? 'https://api.asaas.com/v3' : 'https://api-sandbox.asaas.com/v3';
        return Http::baseUrl($base)->acceptJson()->withHeaders([
            'access_token' => $this->integration->accessToken(),
            'User-Agent' => 'SabaEcommerce/1.0 (payment-integration)',
        ])->connectTimeout(5)->timeout(20);
    }
}
