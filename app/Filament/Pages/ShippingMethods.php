<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Shipping\ToggleShippingIntegrationAction;
use App\Actions\Shipping\UpdateShippingIntegrationAction;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Infrastructure\Shipping\ShippingProviderRegistry;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;

final class ShippingMethods extends Page
{
    protected static ?string $title = 'Meios de Entrega';

    protected static ?string $slug = 'shipping-methods';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.shipping-methods';

    public static function canAccess(): bool
    {
        $user = auth('web')->user();

        return $user instanceof User && $user->canAccessPanel(Filament::getPanel('admin'));
    }

    public function mount(): void
    {
        foreach (['success', 'error'] as $key) {
            if (session()->has($key)) {
                Notification::make()->title((string) session()->pull($key))->status($key === 'success' ? 'success' : 'danger')->send();
            }
        }
    }

    public function getViewData(): array
    {
        return [
            'definitions' => app(ShippingProviderRegistry::class)->definitions(),
            'integrations' => ShippingIntegration::query()->get()->keyBy('provider'),
        ];
    }

    public function configureMelhorEnvioAction(): Action
    {
        return Action::make('configureMelhorEnvio')->label('Configurar')
            ->authorize(fn (): bool => self::canAccess())
            ->modalHeading('Configurar Melhor Envio')->modalWidth(Width::SevenExtraLarge)
            ->modalContent(view('filament.components.melhor-envio-configuration'))
            ->modalSubmitAction(false)->modalCancelActionLabel('Fechar')
            ->closeModalByClickingAway(false)->stickyModalHeader()->stickyModalFooter();
    }

    public function configurePickupAction(): Action
    {
        return Action::make('configurePickup')->label('Configurar')->modalHeading('Retirada na loja')
            ->fillForm(fn () => ShippingIntegration::query()->where('provider', 'local_pickup')->first()?->configuration ?? ['label' => 'Retirada na loja', 'ready_days' => 1])
            ->schema([Section::make('Dados para retirada')->schema([
                TextInput::make('label')->label('Nome exibido')->required()->maxLength(120),
                Textarea::make('address')->label('Endereço da retirada')->required()->maxLength(2000),
                Textarea::make('instructions')->label('Instruções')->maxLength(2000),
                TextInput::make('ready_days')->label('Prazo estimado de preparação (dias úteis)')->integer()->minValue(0)->maxValue(365)->required(),
            ])])->action(function (array $data): void {
                $this->authorizeShipping();
                app(UpdateShippingIntegrationAction::class)->execute('local_pickup', ['is_enabled' => ShippingIntegration::query()->where('provider', 'local_pickup')->value('is_enabled') ?? false, 'environment' => 'production', 'configuration' => $data]);
                Notification::make()->success()->title('Retirada configurada')->send();
            });
    }

    public function toggleProvider(string $provider): void
    {
        $this->authorizeShipping();
        try {
            app(ToggleShippingIntegrationAction::class)->execute($provider);
        } catch (ValidationException $exception) {
            Notification::make()->warning()->title((string) collect($exception->errors())->flatten()->first())->send();
        }
    }

    private function authorizeShipping(): void
    {
        abort_unless(self::canAccess(), 403);
    }
}
