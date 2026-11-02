<?php

declare(strict_types=1);

namespace App\Filament\Resources\Influencers;

use App\Domain\Access\AdminPermissions;
use App\Domain\Influencer\Models\Influencer;
use App\Filament\Resources\Influencers\Pages\CreateInfluencer;
use App\Filament\Resources\Influencers\Pages\EditInfluencer;
use App\Filament\Resources\Influencers\Pages\ListInfluencers;
use App\Filament\Support\RestrictsResourceByGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class InfluencerResource extends Resource
{
    use RestrictsResourceByGroup;

    protected static ?string $model = Influencer::class;
    protected static ?string $modelLabel = 'influenciador';
    protected static ?string $pluralModelLabel = 'Influenciadores';
    protected static string|\UnitEnum|null $navigationGroup = 'Vendas';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'name';

    protected static function permissionKey(): string
    {
        return AdminPermissions::SALES_INFLUENCERS;
    }

    /** Dados de comissão, senha e Pix são gerenciados apenas pelo Administrador. */
    public static function canCreate(): bool
    {
        return self::canViewAny() && auth('web')->user()?->adminGroup?->slug === 'administrador';
    }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return self::canCreate();
    }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Dados e acesso ao portal')->columns(2)->schema([
                TextInput::make('name')->label('Nome')->required()->maxLength(160),
                TextInput::make('email')->label('E-mail de acesso')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('password')->label('Senha de acesso')->password()->revealable()->minLength(10)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Na edição, deixe em branco para manter a senha atual.'),
                Toggle::make('is_active')->label('Influenciador ativo')->default(true),
                TextInput::make('instagram')->label('Instagram (opcional)')->maxLength(255),
                TextInput::make('tiktok')->label('TikTok (opcional)')->maxLength(255),
            ]),
            Section::make('Comissão e repasse')->columns(2)->description('A comissão é calculada somente sobre o valor dos produtos após o desconto, sem frete. A porcentagem é congelada em cada pedido.')->schema([
                TextInput::make('commission_percent')->label('Comissão (%)')->numeric()->required()
                    ->minValue(0)->maxValue(100)->step('0.01')->default(5)
                    ->helperText('Ex.: 5 = 5% de comissão. Altere livremente; pedidos antigos mantêm a porcentagem registrada.'),
                TextInput::make('pix_key')->label('Chave Pix para repasse')->maxLength(255)
                    ->helperText('Acesso restrito ao Administrador. Armazenada criptografada; a transferência financeira é manual.'),
                Textarea::make('notes')->label('Observações internas')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Influenciador')->searchable()->sortable(),
            TextColumn::make('email')->label('E-mail')->searchable(),
            TextColumn::make('commission_bps')->label('Comissão')->formatStateUsing(fn ($state): string => number_format((int) $state / 100, 2, ',', '.').'%'),
            TextColumn::make('coupons_count')->counts('coupons')->label('Cupons'),
            IconColumn::make('is_active')->label('Ativo')->boolean(),
            TextColumn::make('created_at')->label('Cadastrado')->date('d/m/Y'),
        ])->defaultSort('name')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListInfluencers::route('/'), 'create' => CreateInfluencer::route('/create'), 'edit' => EditInfluencer::route('/{record}/edit')];
    }
}
