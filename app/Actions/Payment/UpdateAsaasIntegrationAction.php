<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Domain\Payment\Models\PaymentIntegration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateAsaasIntegrationAction
{
    public function execute(array $input): PaymentIntegration
    {
        $environment = (string) ($input['environment'] ?? '');
        $minutes = (int) ($input['expiration_minutes'] ?? 60);
        $installments = (int) ($input['max_installments'] ?? 1);
        $pix = (bool) ($input['pix'] ?? false);
        $card = (bool) ($input['credit_card'] ?? false);
        $enabled = (bool) ($input['is_enabled'] ?? false);
        if (! in_array($environment, ['sandbox', 'production'], true)) {
            throw ValidationException::withMessages(['environment' => 'Ambiente inválido.']);
        }
        if ($minutes < 10 || $minutes > 1440) {
            throw ValidationException::withMessages(['expiration_minutes' => 'Prazo permitido: de 10 a 1440 minutos.']);
        }
        if ($installments < 1 || $installments > 21) {
            throw ValidationException::withMessages(['max_installments' => 'Parcelamento permitido: de 1 a 21 vezes.']);
        }
        if ($enabled && ! $pix && ! $card) {
            throw ValidationException::withMessages(['payment_methods' => 'Habilite Pix ou cartão antes de ativar.']);
        }
        return DB::transaction(function () use ($input, $environment, $minutes, $installments, $pix, $card, $enabled): PaymentIntegration {
            $integration = PaymentIntegration::query()->firstOrCreate(['provider' => 'asaas', 'environment' => $environment]);
            $credentials = $integration->credentials ?? [];
            foreach (['access_token', 'webhook_secret'] as $key) {
                if (trim((string) ($input[$key] ?? '')) !== '') {
                    $credentials[$key] = trim((string) $input[$key]);
                }
            }
            if ($enabled && (empty($credentials['access_token']) || strlen((string) ($credentials['webhook_secret'] ?? '')) < 32)) {
                throw ValidationException::withMessages(['credentials' => 'Informe a API Key e o token do webhook (ao menos 32 caracteres) antes de ativar.']);
            }
            if (! empty($credentials['webhook_secret']) && PaymentIntegration::query()->where('provider', 'asaas')->whereKeyNot($integration->id)->get()
                ->contains(fn (PaymentIntegration $other) => $other->webhookSecret() === $credentials['webhook_secret'])) {
                throw ValidationException::withMessages(['webhook_secret' => 'Use um token de webhook diferente para cada ambiente Asaas.']);
            }
            if ($enabled) {
                PaymentIntegration::query()->where('is_enabled', true)->whereKeyNot($integration->id)->update(['is_enabled' => false]);
            }
            $integration->update([
                'credentials' => $credentials,
                'configuration' => [
                    'expiration_minutes' => $minutes, 'max_installments' => $installments,
                    'pix' => $pix, 'credit_card' => $card,
                ],
                'is_enabled' => $enabled,
            ]);
            return $integration->refresh();
        }, 3);
    }
}
