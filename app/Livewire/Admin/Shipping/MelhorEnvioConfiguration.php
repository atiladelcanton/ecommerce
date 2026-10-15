<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Shipping;

use App\Actions\Shipping\ConfigureShippingCarrierAction;
use App\Actions\Shipping\DisconnectShippingIntegrationAction;
use App\Actions\Shipping\SyncMelhorEnvioCatalogAction;
use App\Actions\Shipping\TestMelhorEnvioConnectionAction;
use App\Actions\Shipping\UpdateShippingIntegrationAction;
use App\Domain\Shipping\Models\ShippingCarrier;
use App\Domain\Shipping\Models\ShippingIntegration;
use App\Filament\Pages\ShippingMethods;
use App\Infrastructure\Shipping\MelhorEnvio\MelhorEnvioClient;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

final class MelhorEnvioConfiguration extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public string $tab = 'connection';

    public array $connectionData = [];

    public array $operationData = [];

    public array $agencyData = [];

    public array $carrierData = [];

    #[Locked]
    public ?int $activeCarrierId = null;

    #[Locked]
    public string $environment = 'sandbox';

    public function mount(): void
    {
        $this->authorizeShipping();
        $integration = $this->integration();
        $this->environment = $integration?->environment ?? 'sandbox';
        $configuration = $integration?->configuration ?? [];
        $this->connectionForm->fill([
            'environment' => $this->environment,
            'client_id' => $integration?->credentials['client_id'] ?? '',
            'client_secret' => '',
            'application_name' => $configuration['application_name'] ?? 'SABA E-commerce',
            'technical_email' => $configuration['technical_email'] ?? '',
        ]);
        $this->operationForm->fill(array_replace([
            'insurance_mode' => 'always', 'receipt' => false, 'own_hand' => false, 'label_format' => 'pdf',
        ], array_intersect_key($configuration, array_flip(['insurance_mode', 'receipt', 'own_hand', 'label_format']))));
        $this->agencyForm->fill(['country' => 'BR']);
    }

    public function hydrate(): void
    {
        $this->authorizeShipping();
    }

    public function connectionForm(Schema $schema): Schema
    {
        return $schema->statePath('connectionData')->components([
            Section::make('Credenciais da aplicação')->columns(2)->schema([
                Select::make('environment')->label('Ambiente')->options(['sandbox' => 'Sandbox', 'production' => 'Produção'])->required(),
                TextInput::make('application_name')->label('Nome da aplicação')->required()->maxLength(120),
                TextInput::make('client_id')->label('Client ID')->required()->maxLength(255),
                TextInput::make('client_secret')->label('Client Secret')->password()->maxLength(1000)
                    ->helperText('Deixe em branco para manter o valor salvo. O secret salvo nunca é exibido.'),
                TextInput::make('technical_email')->label('E-mail técnico')->email()->required()->maxLength(255),
            ]),
        ]);
    }

    public function operationForm(Schema $schema): Schema
    {
        return $schema->statePath('operationData')->components([
            Section::make('Configurações operacionais')->columns(2)->schema([
                Select::make('insurance_mode')->label('Seguro')->options(['always' => 'Sempre utilizar seguro', 'required' => 'Somente quando obrigatório pelo serviço', 'never' => 'Nunca, quando permitido'])->default('always')->required()->columnSpanFull(),
                Toggle::make('receipt')->label('Aviso de Recebimento')->default(false),
                Toggle::make('own_hand')->label('Mão Própria')->default(false),
                Select::make('label_format')->label('Formato padrão de etiqueta')->options(['pdf' => 'PDF', 'zpl' => 'ZPL', 'jpeg' => 'JPEG'])->default('pdf')->required(),
            ]),
        ]);
    }

    public function agencyForm(Schema $schema): Schema
    {
        return $schema->statePath('agencyData')->components([
            Section::make('Buscar unidades de postagem')->description('Selecione a transportadora e refine a busca por localização. A unidade padrão é definida na configuração de cada transportadora.')->columns(2)->schema([
                Select::make('company')->label('Transportadora')->options(fn () => $this->carriers()->pluck('name', 'external_id')->all())->required(),
                Select::make('country')->label('País')->options(['BR' => 'Brasil'])->default('BR')->required(),
                TextInput::make('state')->label('UF')->placeholder('SP')->helperText('Opcional. Exemplo: SP.'),
                TextInput::make('city')->label('Cidade')->maxLength(255),
            ]),
        ]);
    }

    public function carrierForm(Schema $schema): Schema
    {
        $carrier = $this->activeCarrierId ? $this->carrier($this->activeCarrierId) : null;

        return $schema->statePath('carrierData.'.($carrier?->id ?? 'none'))->components($carrier ? [
            Section::make('Serviços de '.$carrier->name)->schema([
                Toggle::make('is_enabled')->label('Transportadora ativa'),
                CheckboxList::make('services')->label('Exibir no checkout')->options($carrier->services->where('is_available', true)->pluck('name', 'id')->all()),
                CheckboxList::make('automatic')->label('Compra automática de etiqueta')->options($carrier->services->where('is_available', true)->filter(fn ($service) => ($service->metadata['supports_purchase'] ?? true) !== false)->pluck('name', 'id')->all())
                    ->helperText('Habilite apenas serviços homologados para compra comercial via API. Os demais usam preparação manual.'),
                Select::make('agency_id')->label('Unidade de postagem padrão')->placeholder('Nenhuma')->searchable()
                    ->options($carrier->agencies()->where('is_active', true)->where('shipping_integration_id', $carrier->shipping_integration_id)->where('environment', $carrier->environment)->orderBy('name')->pluck('name', 'shipping_agencies.id')->all()),
            ]),
        ] : []);
    }

    public function selectTab(string $tab): void
    {
        $this->authorizeShipping();
        abort_unless(in_array($tab, ['connection', 'carriers', 'agencies', 'operation'], true), 404);
        $this->tab = $tab;
    }

    public function editCarrier(int $id): void
    {
        $carrier = $this->carrier($id);
        $this->activeCarrierId = $id;
        $this->cacheSchema('carrierForm', null);
        if (! array_key_exists($id, $this->carrierData)) {
            $this->carrierForm->fill([
                'is_enabled' => $carrier->is_enabled, 'agency_id' => $carrier->default_shipping_agency_id,
                'services' => $carrier->services->where('is_enabled', true)->pluck('id')->all(),
                'automatic' => $carrier->services->where('fulfillment_mode', 'automatic')->pluck('id')->all(),
            ]);
        }
        $this->tab = 'carriers';
    }

    public function saveConnection(): void
    {
        $this->assertCurrentEnvironment();
        $data = $this->connectionForm->getState();
        $integration = app(UpdateShippingIntegrationAction::class)->execute('melhor_envio', [
            'environment' => $data['environment'],
            'credentials' => ['client_id' => $data['client_id'], 'client_secret' => $data['client_secret'] ?? ''],
            'configuration' => array_intersect_key($data, array_flip(['application_name', 'technical_email'])),
        ]);
        $this->connectionData['client_secret'] = '';
        if ($integration->environment !== $this->environment) {
            $this->carrierData = [];
            $this->activeCarrierId = null;
            $this->agencyData['company'] = null;
            $this->cacheSchema('carrierForm', null);
        }
        $this->environment = $integration->environment;
        $this->saved('Conexão salva');
    }

    public function saveOperation(): void
    {
        $this->assertCurrentEnvironment();
        $data = $this->operationForm->getState();
        app(UpdateShippingIntegrationAction::class)->execute('melhor_envio', ['configuration' => $data]);
        $this->saved('Configurações operacionais salvas');
    }

    public function saveCarrier(): void
    {
        $this->assertCurrentEnvironment();
        $carrier = $this->carrier($this->activeCarrierId ?? 0);
        $data = $this->carrierForm->getState();
        $enabled = array_map('intval', $data['services'] ?? []);
        $automatic = array_map('intval', $data['automatic'] ?? []);
        try {
            app(ConfigureShippingCarrierAction::class)->execute($carrier, [
                'is_enabled' => (bool) ($data['is_enabled'] ?? false), 'agency_id' => $data['agency_id'] ?? null,
                'services' => $carrier->services->map(fn ($service) => ['id' => $service->id, 'is_enabled' => in_array($service->id, $enabled, true), 'fulfillment_mode' => in_array($service->id, $automatic, true) ? 'automatic' : 'manual'])->all(),
            ]);
        } catch (ValidationException $exception) {
            $this->validationErrors($exception, 'carrierData.'.$carrier->id);
        }
        $this->saved('Transportadora atualizada');
    }

    public function syncCarriers(): void
    {
        $this->assertCurrentEnvironment();
        $this->runOperation(fn () => app(SyncMelhorEnvioCatalogAction::class)->execute(), 'Transportadoras e serviços atualizados', 'Não foi possível atualizar as transportadoras do Melhor Envio. Verifique a conexão e tente novamente.');
    }

    public function syncAgencies(): void
    {
        $this->assertCurrentEnvironment();
        $data = $this->agencyForm->getState();
        $this->runOperation(fn () => app(SyncMelhorEnvioCatalogAction::class)->agencies($data), 'Unidades atualizadas', 'Não foi possível atualizar as unidades do Melhor Envio. Verifique a conexão e tente novamente.', 'agencyData');
        $this->cacheSchema('carrierForm', null);
    }

    public function updateCarrierAgencies(int $id): void
    {
        $carrier = $this->carrier($id);
        $this->agencyData['company'] = $carrier->external_id;
        $this->tab = 'agencies';
    }

    public function testConnection(): void
    {
        $this->assertCurrentEnvironment();
        $this->runOperation(fn () => app(TestMelhorEnvioConnectionAction::class)->execute(), 'Conexão validada', 'Não foi possível testar a conexão com o Melhor Envio.');
    }

    public function connect(): void
    {
        $this->saveConnection();
        $this->redirectRoute('admin.shipping.melhor-envio.connect');
    }

    public function disconnect(): void
    {
        $this->assertCurrentEnvironment();
        app(DisconnectShippingIntegrationAction::class)->execute('melhor_envio');
        $this->saved('Conta desconectada');
    }

    public function render(): View
    {
        $this->authorizeShipping();
        $integration = $this->integration();

        return view('livewire.admin.shipping.melhor-envio-configuration', [
            'connected' => filled($integration?->connected_at) && filled($integration?->access_token),
            'expiresAt' => $integration?->token_expires_at,
            'environmentLabel' => $this->environment === 'production' ? 'Produção' : 'Sandbox',
            'callback' => app(MelhorEnvioClient::class)->callbackUrl(),
            'webhook' => route('webhooks.shipping.melhor-envio'),
            'carriers' => $this->carriers(),
        ]);
    }

    private function carriers(): Collection
    {
        return ShippingCarrier::query()->with(['services', 'defaultAgency'])->where('shipping_integration_id', $this->integration()?->id)
            ->where('environment', $this->environment)->orderBy('name')->get();
    }

    private function carrier(int $id): ShippingCarrier
    {
        $this->assertCurrentEnvironment();

        return $this->carriers()->find($id) ?? abort(404);
    }

    private function integration(): ?ShippingIntegration
    {
        return ShippingIntegration::query()->where('provider', 'melhor_envio')->first();
    }

    private function authorizeShipping(): void
    {
        abort_unless(ShippingMethods::canAccess(), 403);
    }

    private function assertCurrentEnvironment(): void
    {
        $this->authorizeShipping();
        if (($this->integration()?->environment ?? 'sandbox') !== $this->environment) {
            throw ValidationException::withMessages(['shipping' => 'O ambiente foi alterado em outra sessão. Feche e reabra a configuração.']);
        }
    }

    private function saved(string $message): void
    {
        Notification::make()->success()->title($message)->send();
    }

    private function validationErrors(ValidationException $exception, string $path): never
    {
        $errors = [];
        foreach ($exception->errors() as $key => $messages) {
            $errors[$key === 'shipping' ? 'shipping' : $path.'.'.$key] = $messages;
        }
        throw ValidationException::withMessages($errors);
    }

    private function runOperation(callable $operation, string $success, string $failure, string $validationPath = ''): void
    {
        $this->resetValidation();
        try {
            $operation();
            $this->saved($success);
        } catch (ValidationException $exception) {
            $this->validationErrors($exception, $validationPath);
        } catch (Throwable $exception) {
            Log::warning('Shipping administration operation failed', ['provider' => 'melhor_envio', 'exception_type' => $exception::class]);
            Notification::make()->danger()->title($failure)->send();
        }
    }
}
