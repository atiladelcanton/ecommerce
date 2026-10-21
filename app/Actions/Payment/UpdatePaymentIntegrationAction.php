<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Domain\Payment\Models\PaymentIntegration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePaymentIntegrationAction
{
    public function execute(array $input): PaymentIntegration
    {
        $env = (string) ($input['environment'] ?? '');
        if (! in_array($env, ['sandbox', 'production'], true)) {
            throw ValidationException::withMessages(['environment' => 'Ambiente inválido.']);
        }
        $hours = (int) ($input['expiration_hours'] ?? 72);
        if ($hours < 1 || $hours > 168) {
            throw ValidationException::withMessages(['expiration_hours' => 'Defina uma validade entre 1 e 168 horas.']);
        }
        $enabled = (bool) ($input['is_enabled'] ?? false);
        $offline = (bool) ($input['allow_offline'] ?? false);
        if ($offline && $hours < 72) {
            throw ValidationException::withMessages(['expiration_hours' => 'Pagamentos offline exigem pelo menos 72 horas para compensação.']);
        }
        return DB::transaction(function () use ($input, $env, $hours, $enabled, $offline): PaymentIntegration {
            $integration = PaymentIntegration::query()->firstOrCreate(['provider' => 'mercado_pago', 'environment' => $env]);
            $credentials = $integration->credentials ?? [];
            foreach (['access_token', 'webhook_secret'] as $key) {
                if (trim((string) ($input[$key] ?? '')) !== '') {
                    $credentials[$key] = trim((string) $input[$key]);
                }
            }
            if ($enabled && (empty($credentials['access_token']) || empty($credentials['webhook_secret']))) {
                throw ValidationException::withMessages(['credentials' => 'Informe o Access Token e o segredo de assinatura do webhook antes de ativar.']);
            }
            if ($enabled) {
                PaymentIntegration::query()->where('is_enabled', true)->whereKeyNot($integration->id)->update(['is_enabled' => false]);
            }
            $integration->update([
                'credentials' => $credentials, 'configuration' => ['expiration_hours' => $hours, 'allow_offline' => $offline],
                'is_enabled' => $enabled,
            ]);
            return $integration->refresh();
        }, 3);
    }
}
