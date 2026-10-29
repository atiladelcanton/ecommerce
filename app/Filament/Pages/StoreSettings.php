<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Settings\UpdateStoreSettingsAction;
use App\Domain\Settings\Models\StoreSetting;
use App\Filament\Support\CatalogForm;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use App\Domain\Access\AdminPermissions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class StoreSettings extends Page
{
    protected static ?string $title = 'Configurações da loja';

    protected static ?string $slug = 'store-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.store-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return AdminPermissions::check(AdminPermissions::SETTINGS_STORE);
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
        $this->form->fill(StoreSetting::query()->find(StoreSetting::MAIN_ID)?->toArray() ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->columns(1)->components([
            Section::make('Identidade')->columns(['default' => 1, 'lg' => 3])->schema([
                TextInput::make('store_name')->label('Nome da loja')->required()->maxLength(255),
                TextInput::make('legal_name')->label('Razão social')->maxLength(255),
                TextInput::make('state_register')->label('Inscrição Estadual')->maxLength(40),
                TextInput::make('economic_activity_code')->label('CNAE')->maxLength(20),
                TextInput::make('cnpj')->label('CNPJ')->maxLength(20),
            ]),
            Section::make('Identidade visual')
                ->description('Personalize a marca da sua loja. O modelo de template continua sendo definido exclusivamente pelo responsável técnico.')
                ->columns(['default' => 1, 'lg' => 3])
                ->schema([
                    FileUpload::make('logo_path')->label('Logotipo da loja')->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->disk('public')->directory('store/branding')->visibility('public')->maxSize(5120)
                        ->preventFilePathTampering(allowFilePathUsing: static fn (string $file): bool => StoreSetting::query()->whereKey(StoreSetting::MAIN_ID)->where('logo_path', $file)->exists())
                        ->helperText('JPG, PNG ou WebP, até 5 MB. Preferencialmente imagem horizontal com fundo transparente. Remova para restaurar a logo padrão.')
                        ->columnSpanFull(),
                    ColorPicker::make('color_primary')->label('Cor primária')
                        ->helperText('Destaques, links, ícones e promoções. Deixe vazio para usar a cor original do template.'),
                    ColorPicker::make('color_secondary')->label('Cor secundária')
                        ->helperText('Faixas superiores e elementos de apoio. Deixe vazio para usar a cor original.'),
                    ColorPicker::make('color_button')->label('Cor dos botões')
                        ->helperText('Botões de compra e ações principais. O texto recebe contraste automático.'),
                ]),
            Section::make('Contato')->columns(['default' => 1, 'lg' => 3])->schema([
                TextInput::make('phone')->label('Telefone')->type('tel')->inputMode('tel')->maxLength(30),
                TextInput::make('whatsapp')->label('WhatsApp')->type('tel')->inputMode('tel')->maxLength(30)->helperText('Inclua código do país e DDD.'),
                TextInput::make('email')->label('E-mail')->email()->maxLength(255),
            ]),
            Section::make('Endereço')->columns(['default' => 1, 'lg' => 3])->schema([
                TextInput::make('zip_code')->label('CEP')->maxLength(9)->placeholder('00000-000'),
                TextInput::make('street')->label('Logradouro')->maxLength(255),
                TextInput::make('number')->label('Número')->maxLength(30),
                TextInput::make('complement')->label('Complemento')->maxLength(255),
                TextInput::make('neighborhood')->label('Bairro')->maxLength(255),
                TextInput::make('city')->label('Cidade')->maxLength(255),
                TextInput::make('state')->label('UF')->maxLength(2)->placeholder('SP'),
            ]),
            Section::make('Redes sociais')->columns(['default' => 1, 'lg' => 3])->schema([
                TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(2048),
                TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(2048),
                TextInput::make('youtube_url')->label('YouTube')->url()->maxLength(2048),
            ]),
            Section::make('SEO')->schema([
                TextInput::make('seo_title')->label('Título SEO')->maxLength(255),
                Textarea::make('seo_description')->label('Descrição SEO')->maxLength(5000)->columnSpanFull(),
            ]),
            Section::make('Blog')
                ->description('Ative a área de conteúdos do site e escolha se deseja destacar as postagens na página inicial.')
                ->schema([
                    Toggle::make('blog_enabled')->label('Ativar blog no e-commerce')
                        ->helperText('Mostra o link Blog nos menus e permite acesso às postagens publicadas. Desativado: /blog retorna página não encontrada.')
                        ->default(false)->live(),
                    Toggle::make('blog_show_on_home')->label('Exibir "Últimas do Blog" na página inicial')
                        ->helperText('Opção independente: só aparece quando o blog também está ativo e existem postagens publicadas.')
                        ->default(false),
                ]),
            Section::make('Frete')->schema([
                TextInput::make('shipping_origin_zip_code')->label('CEP de origem')->maxLength(9)->placeholder('00000-000'),
            ]),
        ]);
    }

    public function save(): void
    {
        abort_unless(self::canAccess(), 403);
        $data = $this->form->getState();
        $settings = CatalogForm::submit(fn (): StoreSetting => app(UpdateStoreSettingsAction::class)->execute($data));
        $this->form->fill($settings->toArray());
        Notification::make()->success()->title('Configurações salvas')->send();
    }
}
