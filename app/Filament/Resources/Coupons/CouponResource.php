<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Coupon\Models\Coupon;
use App\Filament\Forms\Components\BrazilianMoneyInput;
use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Support\BrazilianMoney;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $modelLabel = 'cupom';
    protected static ?string $pluralModelLabel = 'Cupons de desconto';
    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Cupom')->columns(['default' => 1, 'lg' => 2])->schema([
                TextInput::make('code')->label('Código digitado pelo cliente')->required()->maxLength(64)->helperText('Letras, números, hífen e _; o código será salvo em maiúsculas.'),
                TextInput::make('description')->label('Descrição interna')->maxLength(255),
                Select::make('discount_type')->label('Tipo de desconto')->options(['percentage' => 'Percentual', 'fixed' => 'Valor fixo'])->required()->live()->default('percentage'),
                TextInput::make('percentage_value')->label('Desconto (%)')->integer()->minValue(1)->maxValue(100)->visible(fn (Get $get): bool => $get('discount_type') === 'percentage'),
                BrazilianMoneyInput::make('fixed_value_cents')->label('Desconto (R$)')->visible(fn (Get $get): bool => $get('discount_type') === 'fixed'),
                Toggle::make('is_active')->label('Cupom ativo')->default(false),
            ]),
            Section::make('Produtos elegíveis')->columns(['default' => 1, 'lg' => 2])->schema([
                Select::make('scope')->label('Aplicação')->options(['all' => 'Toda a loja', 'categories' => 'Categorias selecionadas', 'products' => 'Produtos selecionados'])->required()->default('all')->live(),
                Toggle::make('allow_sale_items')->label('Aplicar também em produtos promocionais')->default(false),
                Select::make('category_ids')->label('Categorias')->multiple()->searchable()
                    ->options(fn (): array => Category::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn (Get $get): bool => $get('scope') === 'categories')->columnSpanFull(),
                Select::make('product_ids')->label('Produtos')->multiple()->searchable()
                    ->options(fn (): array => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->visible(fn (Get $get): bool => $get('scope') === 'products')->columnSpanFull(),
            ]),
            Section::make('Limites e validade')->columns(['default' => 1, 'lg' => 2])->schema([
                BrazilianMoneyInput::make('minimum_subtotal_cents')->label('Compra mínima (R$)')->helperText('Opcional, sem o frete.'),
                BrazilianMoneyInput::make('maximum_discount_cents')->label('Desconto máximo (R$)')->helperText('Opcional.'),
                TextInput::make('usage_limit')->label('Limite total de utilizações')->integer()->minValue(1)->helperText('Vazio significa sem limite geral; cada CPF/CNPJ pode usar uma única vez.'),
                DateTimePicker::make('starts_at')->label('Início')->seconds(false),
                DateTimePicker::make('ends_at')->label('Término')->seconds(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->searchable()->sortable(),
            TextColumn::make('discount_type')->label('Desconto')->formatStateUsing(fn (string $state, Coupon $record): string =>
                $state === 'percentage' ? $record->value.'%' : BrazilianMoney::display($record->value)),
            TextColumn::make('scope')->label('Aplicação')->formatStateUsing(fn (string $state): string => match ($state) {
                'all' => 'Toda a loja', 'products' => 'Produtos', 'categories' => 'Categorias', default => $state,
            }),
            TextColumn::make('claims_count')->label('Usos/reservas')->counts('claims'),
            IconColumn::make('is_active')->label('Ativo')->boolean(),
            TextColumn::make('ends_at')->label('Término')->dateTime('d/m/Y H:i')->placeholder('Sem prazo'),
        ])->recordActions([EditAction::make()->iconButton()])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListCoupons::route('/'), 'create' => CreateCoupon::route('/create'), 'edit' => EditCoupon::route('/{record}/edit')];
    }
}
