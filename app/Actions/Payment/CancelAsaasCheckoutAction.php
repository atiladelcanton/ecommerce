<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentIntegration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Request provider cancellation; inventory remains held until confirmed by Asaas webhook. */
final class CancelAsaasCheckoutAction
{
    public function execute(Payment $payment): void
    {
        if ($payment->gateway !== 'asaas' || $payment->status !== PaymentStatus::PENDING
            || ! preg_match('/^[A-Za-z0-9_-]{12,100}$/', $payment->external_id)
            || str_starts_with($payment->external_id, 'initiating-')) {
            throw ValidationException::withMessages(['payment' => 'Esta cobrança não pode ser cancelada por esta operação.']);
        }
        $integration = PaymentIntegration::query()->findOrFail($payment->payment_integration_id);
        if ($integration->provider !== 'asaas' || $payment->environment !== $integration->environment) {
            throw new \RuntimeException('Ambiente histórico da cobrança inválido.');
        }
        $base = $integration->environment === 'production'
            ? 'https://api.asaas.com/v3' : 'https://api-sandbox.asaas.com/v3';
        try {
            $response = Http::baseUrl($base)->acceptJson()->withHeaders([
                'access_token' => $integration->accessToken(),
                'User-Agent' => 'SabaEcommerce/1.0 (payment-integration)',
            ])->connectTimeout(5)->timeout(20)->post('/checkouts/'.rawurlencode($payment->external_id).'/cancel');
        } catch (ConnectionException $exception) {
            throw new \RuntimeException('Resultado do cancelamento inconclusivo. Consulte o painel Asaas.', previous: $exception);
        }
        if (! $response->successful()) {
            Log::warning('Asaas rejected cancel checkout request.', ['payment_id' => $payment->id, 'status' => $response->status()]);
            throw ValidationException::withMessages(['payment' => 'Não foi possível solicitar o cancelamento. Confira a situação no Asaas.']);
        }
        $payment->update([
            'needs_review' => true,
            'review_reason' => 'Cancelamento solicitado ao Asaas; aguarde o webhook de confirmação. Reserva mantida.',
        ]);
    }
}
