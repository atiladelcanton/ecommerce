<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Payment\UpdateAsaasIntegrationAction;
use App\Actions\Payment\CancelAsaasCheckoutAction;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Models\PaymentIntegration;
use App\Infrastructure\Payments\AsaasGateway;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;

final class PaymentMethods extends Page
{
    protected static ?string $title = 'Meios de Pagamento';

    protected static ?string $slug = 'payment-methods';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.payment-methods';

    public string $environment = 'sandbox';

    public string $accessToken = '';

    public string $webhookSecret = '';

    public bool $isEnabled = false;

    public bool $pix = true;

    public bool $creditCard = true;

    public int $expirationMinutes = 60;

    public int $maxInstallments = 6;

    public static function canAccess(): bool
    {
        $user = auth('web')->user();
        return $user instanceof User && $user->canAccessPanel(Filament::getPanel('admin'));
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->loadEnvironment();
    }

    public function updatedEnvironment(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->loadEnvironment();
    }

    private function loadEnvironment(): void
    {
        $integration = PaymentIntegration::query()->where('provider', 'asaas')->where('environment', $this->environment)->first();
        $configuration = $integration?->configuration ?? [];
        $this->isEnabled = $integration?->is_enabled ?? false;
        $this->expirationMinutes = (int) ($configuration['expiration_minutes'] ?? 60);
        $this->maxInstallments = (int) ($configuration['max_installments'] ?? 6);
        $this->pix = (bool) ($configuration['pix'] ?? true);
        $this->creditCard = (bool) ($configuration['credit_card'] ?? true);
        $this->accessToken = '';
        $this->webhookSecret = '';
    }

    public function save(UpdateAsaasIntegrationAction $action): void
    {
        abort_unless(self::canAccess(), 403);
        $this->validate([
            'environment' => 'required|in:sandbox,production',
            'expirationMinutes' => 'required|integer|between:10,1440',
            'maxInstallments' => 'required|integer|between:1,21',
            'accessToken' => 'nullable|string|max:4096',
            'webhookSecret' => 'nullable|string|min:32|max:4096',
            'isEnabled' => 'boolean', 'pix' => 'boolean', 'creditCard' => 'boolean',
        ]);
        try {
            $action->execute([
                'environment' => $this->environment, 'expiration_minutes' => $this->expirationMinutes,
                'max_installments' => $this->maxInstallments, 'pix' => $this->pix,
                'credit_card' => $this->creditCard, 'is_enabled' => $this->isEnabled,
                'access_token' => $this->accessToken, 'webhook_secret' => $this->webhookSecret,
            ]);
            $this->loadEnvironment();
            Notification::make()->success()->title('Configuração Asaas salva.')->send();
        } catch (ValidationException $exception) {
            Notification::make()->warning()->title((string) collect($exception->errors())->flatten()->first())->send();
        }
    }

    public function testConnection(): void
    {
        abort_unless(self::canAccess(), 403);
        $integration = PaymentIntegration::query()->where('provider', 'asaas')->where('environment', $this->environment)->first();
        if (! $integration || $integration->accessToken() === '') {
            Notification::make()->warning()->title('Salve a API Key antes de testar.')->send();
            return;
        }
        try {
            $ok = (new AsaasGateway($integration))->testConnection();
            $integration->update(['last_tested_at' => now()]);
            Notification::make()->title($ok ? 'Conexão com Asaas confirmada.' : 'Não foi possível validar a API Key.')
                ->status($ok ? 'success' : 'warning')->send();
        } catch (\Throwable $exception) {
            Notification::make()->danger()->title('Falha ao consultar o Asaas. Verifique o token e a rede.')->send();
        }
    }

    public function cancelCheckout(int $paymentId, CancelAsaasCheckoutAction $action): void
    {
        abort_unless(self::canAccess(), 403);
        $payment = Payment::query()->findOrFail($paymentId);
        abort_unless($payment->gateway === 'asaas', 404);
        try {
            $action->execute($payment);
            Notification::make()->warning()->title('Cancelamento solicitado. A reserva será liberada somente após confirmação do Asaas.')->send();
        } catch (\Throwable $exception) {
            Notification::make()->danger()->title('Não foi possível cancelar automaticamente. Verifique a cobrança no Asaas.')->send();
        }
    }

    public function getViewData(): array
    {
        return [
            'integrations' => PaymentIntegration::query()->where('provider', 'asaas')->get()->keyBy('environment'),
            'recentPayments' => Payment::query()->with('order')->where('gateway', 'asaas')->latest()->limit(25)->get(),
        ];
    }
}
