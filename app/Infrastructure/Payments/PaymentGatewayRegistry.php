<?php

declare(strict_types=1);

namespace App\Infrastructure\Payments;

use App\Domain\Payment\Contracts\PaymentGateway;
use App\Domain\Payment\Models\PaymentIntegration;
use Illuminate\Validation\ValidationException;

final class PaymentGatewayRegistry
{
    public function active(): ?PaymentIntegration
    {
        return PaymentIntegration::query()->where('is_enabled', true)
            ->whereIn('provider', app()->environment('testing') && config('payments.allow_legacy_test_driver', false)
                ? ['asaas', 'mercado_pago'] : ['asaas'])->orderBy('id')->first();
    }

    public function gateway(PaymentIntegration $integration): PaymentGateway
    {
        return match ($integration->provider) {
            'mercado_pago' => new MercadoPagoGateway($integration), // Histórico: não oferecido no checkout.
            'asaas' => new AsaasGateway($integration),
            default => throw ValidationException::withMessages(['payment' => 'Este meio de pagamento ainda não foi integrado.']),
        };
    }

    public function fakeAllowed(): bool
    {
        return app()->environment(['local', 'testing', 'development']) && config('payments.default') === 'fake';
    }
}
