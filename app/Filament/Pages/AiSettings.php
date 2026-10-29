<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\AI\TestAiConnectionAction;
use App\Actions\AI\UpdateAiSettingsAction;
use App\Domain\AI\Exceptions\AiProviderFailed;
use App\Domain\AI\Models\AiSetting;
use App\Domain\AI\Models\AiUsageLog;
use App\Filament\Support\CatalogForm;
use App\Infrastructure\AI\AiProviderRegistry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use App\Domain\Access\AdminPermissions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;

final class AiSettings extends Page
{
    protected static ?string $title = 'Inteligência Artificial';

    protected static ?string $slug = 'ai-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.ai-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return AdminPermissions::check(AdminPermissions::SETTINGS_AI);
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
        $settings = AiSetting::current();
        $state = $settings->toArray();
        $state['api_key'] = null;
        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->columns(1)->components([
            Section::make('Assistente de IA')
                ->description('A IA ajuda a escrever e revisar o cadastro de produtos. Ela nunca altera preço, estoque, SKU, medidas ou variações automaticamente.')
                ->columns(['default' => 1, 'lg' => 2])
                ->schema([
                    Toggle::make('is_enabled')->label('Ativar assistente de IA')->default(false),
                    Select::make('provider')->label('Provedor')->options(fn (): array => app(AiProviderRegistry::class)->implementedOptions())->required()->default('groq'),
                    Select::make('model')->label('Modelo')->options(fn (): array => (array) config('ai.groq.models', []))->required()->default('openai/gpt-oss-120b')->helperText('Os modelos são executados pelo Groq usando a sua chave Groq. O GPT-OSS 120B é o padrão recomendado para textos de produto.'),
                    TextInput::make('api_key')->label('API Key')->password()->revealable()
                        ->helperText(fn (): string => filled(AiSetting::current()->api_key) ? 'Já existe uma chave configurada. Deixe vazio para mantê-la.' : 'Crie uma chave gratuita no Groq e cole aqui.'),
                    TextInput::make('monthly_request_limit')->label('Limite mensal interno de gerações')->integer()->minValue(1)->maxValue(1000000)
                        ->helperText('Opcional. Deixe vazio para não impor limite interno. Os limites do plano do provedor continuam valendo.'),
                ]),
            Section::make('Orientações de escrita')
                ->description('Regras adicionais que serão aplicadas em todos os textos gerados para esta loja.')
                ->schema([
                    Textarea::make('store_instructions')->label('Instruções da loja')->rows(6)->maxLength(10000)
                        ->placeholder('Ex.: use um tom técnico e objetivo; não use emojis; destaque aplicações reais sem inventar especificações.')
                        ->helperText('A regra de não inventar características técnicas é aplicada pelo sistema independentemente deste campo.'),
                ]),
        ]);
    }

    public function save(): void
    {
        abort_unless(self::canAccess(), 403);
        $data = $this->form->getState();
        $settings = CatalogForm::submit(fn (): AiSetting => app(UpdateAiSettingsAction::class)->execute($data));
        $state = $settings->toArray();
        $state['api_key'] = null;
        $this->form->fill($state);
        Notification::make()->success()->title('Configurações de IA salvas')->send();
    }

    public function saveAndTest(): void
    {
        abort_unless(self::canAccess(), 403);

        try {
            $data = $this->form->getState();
            $settings = CatalogForm::submit(fn (): AiSetting => app(UpdateAiSettingsAction::class)->execute($data));
            app(TestAiConnectionAction::class)->execute($settings);
            $state = $settings->toArray();
            $state['api_key'] = null;
            $this->form->fill($state);
            Notification::make()->success()->title('Conexão com o Groq funcionando')->body('A chave foi aceita e o assistente está pronto para uso.')->send();
        } catch (AiProviderFailed $exception) {
            Notification::make()->danger()->title('Não foi possível conectar')->body($exception->getMessage())->persistent()->send();
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        $settings = AiSetting::current();
        $query = AiUsageLog::query()->where('created_at', '>=', now()->startOfMonth());

        return [
            'settingsRecord' => $settings,
            'usageCount' => (clone $query)->count(),
            'successfulCount' => (clone $query)->where('successful', true)->count(),
            'inputTokens' => (int) (clone $query)->sum('input_tokens'),
            'outputTokens' => (int) (clone $query)->sum('output_tokens'),
        ];
    }
}
